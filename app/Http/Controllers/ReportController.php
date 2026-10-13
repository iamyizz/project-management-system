<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\EndUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const STATUSES = ['planning', 'progress', 'done', 'cancel'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'pic_id' => ['nullable', 'integer', 'exists:users,id'],
            'end_user_id' => ['nullable', 'integer', 'exists:end_users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $projectQuery = Project::query()
            ->with(['endUser:id,nama', 'pic:id,nama'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['pic_id'] ?? null, fn ($query, $picId) => $query->where('pic_id', (int) $picId))
            ->when($filters['end_user_id'] ?? null, fn ($query, $endUserId) => $query->where('end_user_id', (int) $endUserId))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('deadline_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('deadline_date', '<=', $date));

        $summary = (clone $projectQuery)
            ->selectRaw('COUNT(*) as total_projects, COALESCE(SUM(project_value), 0) as total_value, COALESCE(SUM(internal_cost), 0) as total_cost, COALESCE(SUM(margin), 0) as total_margin')
            ->first();

        $statusCounts = (clone $projectQuery)
            ->reorder()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusColors = [
            'planning' => '#f59e0b',
            'progress' => '#6366f1',
            'done' => '#10b981',
            'cancel' => '#f43f5e',
        ];
        $totalForChart = max(1, (int) $summary->total_projects);
        $statusSegments = collect(self::STATUSES)->map(function (string $status) use ($statusCounts, $statusColors, $totalForChart) {
            $count = (int) ($statusCounts[$status] ?? 0);

            return [
                'status' => $status,
                'count' => $count,
                'percent' => round(($count / $totalForChart) * 100, 1),
                'color' => $statusColors[$status],
            ];
        });

        $trendStart = now()->startOfMonth()->subMonths(11);
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
        $monthlyCounts = (clone $projectQuery)
            ->where('created_at', '>=', $trendStart)
            ->reorder()
            ->selectRaw("{$monthExpression} as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');
        $monthlyProjects = collect(range(0, 11))->map(function (int $offset) use ($trendStart, $monthlyCounts) {
            $month = $trendStart->copy()->addMonths($offset);
            $key = $month->format('Y-m');

            return [
                'label' => $month->translatedFormat('M y'),
                'count' => (int) ($monthlyCounts[$key] ?? 0),
            ];
        });

        $projects = (clone $projectQuery)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pics = User::query()
            ->where('role', 'user')
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $endUsers = EndUser::query()->orderBy('nama')->get(['id', 'nama']);

        $actionable = (clone $projectQuery)
            ->whereNotIn('status', ['done', 'cancel']);
        $overdueCount = (clone $actionable)->whereNotNull('deadline_date')->whereDate('deadline_date', '<', today())->count();
        $upcomingCount = (clone $actionable)->whereNotNull('deadline_date')
            ->whereBetween('deadline_date', [today(), today()->copy()->addDays(30)])->count();
        $marginPercentage = (float) $summary->total_value > 0
            ? ((float) $summary->total_margin / (float) $summary->total_value) * 100
            : 0;

        return view('admin.reports.index', compact('filters', 'summary', 'statusCounts', 'statusSegments', 'monthlyProjects', 'projects', 'pics', 'endUsers', 'overdueCount', 'upcomingCount', 'marginPercentage'));
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'pic_id' => ['nullable', 'integer', 'exists:users,id'],
            'end_user_id' => ['nullable', 'integer', 'exists:end_users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $projects = Project::query()
            ->with(['endUser:id,nama', 'pic:id,nama'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['pic_id'] ?? null, fn ($query, $picId) => $query->where('pic_id', (int) $picId))
            ->when($filters['end_user_id'] ?? null, fn ($query, $endUserId) => $query->where('end_user_id', (int) $endUserId))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('deadline_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('deadline_date', '<=', $date))
            ->orderBy('project_name')
            ->get();

        return response()->streamDownload(function () use ($projects): void {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Project', 'End User', 'PIC', 'Status', 'Deadline', 'Project Value', 'Internal Cost', 'Margin', 'Margin (%)']);
            $safeCell = static function (?string $value): ?string {
                return $value !== null && preg_match('/^[=+@\-\t\r]/', $value) === 1
                    ? "'{$value}"
                    : $value;
            };

            foreach ($projects as $project) {
                fputcsv($stream, [
                    $safeCell($project->project_name),
                    $safeCell($project->endUser?->nama),
                    $safeCell($project->pic?->nama),
                    $safeCell(ucfirst($project->status)),
                    $project->deadline_date?->format('Y-m-d'),
                    $project->project_value,
                    $project->internal_cost,
                    $project->margin,
                    $project->percentage,
                ]);
            }

            fclose($stream);
        }, 'laporan-project-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
