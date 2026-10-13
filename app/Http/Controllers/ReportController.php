<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Carbon;
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
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $projectQuery = Project::query()
            ->with(['endUser:id,nama', 'pic:id,nama'])
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['pic_id'] ?? null, fn ($query, $picId) => $query->where('pic_id', (int) $picId))
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

        return view('admin.reports.index', compact('filters', 'summary', 'statusCounts', 'statusSegments', 'monthlyProjects', 'projects', 'pics'));
    }
}
