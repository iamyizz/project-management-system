<?php

namespace App\Services;

use App\Models\EndUser;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    private const STATUSES = ['planning', 'progress', 'done', 'cancel'];

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function buildReportData(array $filters): array
    {
        $projectQuery = $this->filteredProjects($filters);
        $summary = (clone $projectQuery)
            ->selectRaw('COUNT(*) as total_projects, COALESCE(SUM(project_value), 0) as total_value, COALESCE(SUM(internal_cost), 0) as total_cost, COALESCE(SUM(margin), 0) as total_margin')
            ->first();
        $statusCounts = $this->statusCounts($projectQuery);
        $statusSegments = $this->statusSegments($statusCounts, (int) $summary->total_projects);
        $monthlyFinancials = $this->monthlyFinancials($projectQuery);
        $projects = $this->applyTableFilters(clone $projectQuery, $filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $pics = User::query()->where('role', 'user')->orderBy('nama')->get(['id', 'nama']);
        $endUsers = EndUser::query()->orderBy('nama')->get(['id', 'nama']);
        $actionable = (clone $projectQuery)->whereNotIn('status', ['done', 'cancel']);
        $overdueCount = (clone $actionable)
            ->whereNotNull('deadline_date')
            ->whereDate('deadline_date', '<', today())
            ->count();
        $upcomingCount = (clone $actionable)
            ->whereNotNull('deadline_date')
            ->whereBetween('deadline_date', [today(), today()->copy()->addDays(30)])
            ->count();
        $upcomingProjects = (clone $actionable)
            ->whereNotNull('deadline_date')
            ->whereDate('deadline_date', '>=', today())
            ->orderBy('deadline_date')
            ->limit(5)
            ->get();
        $marginPercentage = (float) $summary->total_value > 0
            ? ((float) $summary->total_margin / (float) $summary->total_value) * 100
            : 0;

        return compact(
            'filters',
            'summary',
            'statusCounts',
            'statusSegments',
            'monthlyFinancials',
            'projects',
            'pics',
            'endUsers',
            'overdueCount',
            'upcomingCount',
            'upcomingProjects',
            'marginPercentage',
        );
    }

    /** @param array<string, mixed> $filters
     *  @return Collection<int, Project>
     */
    public function exportProjects(array $filters): Collection
    {
        return $this->applyTableFilters($this->filteredProjects($filters), $filters)
            ->orderBy('project_name')
            ->get();
    }

    /** @param array<string, mixed> $filters */
    private function filteredProjects(array $filters): Builder
    {
        return Project::query()
            ->with(['endUser:id,nama', 'pic:id,nama'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['pic_id'] ?? null, fn (Builder $query, $picId) => $query->where('pic_id', (int) $picId))
            ->when($filters['end_user_id'] ?? null, fn (Builder $query, $endUserId) => $query->where('end_user_id', (int) $endUserId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('deadline_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('deadline_date', '<=', $date));
    }

    /** @param array<string, mixed> $filters */
    private function applyTableFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(($filters['deadline_scope'] ?? null) === 'overdue', function (Builder $query): void {
                $query->whereNotIn('status', ['done', 'cancel'])
                    ->whereNotNull('deadline_date')
                    ->whereDate('deadline_date', '<', today());
            })
            ->when(($filters['deadline_scope'] ?? null) === 'upcoming', function (Builder $query): void {
                $query->whereNotIn('status', ['done', 'cancel'])
                    ->whereNotNull('deadline_date')
                    ->whereBetween('deadline_date', [today(), today()->copy()->addDays(30)]);
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $escapedSearch = ProjectService::escapeLikeWildcards($search);
                $query->where(function (Builder $query) use ($escapedSearch): void {
                    $query->where('project_name', 'like', "%{$escapedSearch}%")
                        ->orWhereHas('endUser', fn (Builder $relation) => $relation->where('nama', 'like', "%{$escapedSearch}%"))
                        ->orWhereHas('pic', fn (Builder $relation) => $relation->where('nama', 'like', "%{$escapedSearch}%"));
                });
            });
    }

    /** @return \Illuminate\Support\Collection<string, int> */
    private function statusCounts(Builder $projectQuery): \Illuminate\Support\Collection
    {
        return (clone $projectQuery)
            ->reorder()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    /** @param \Illuminate\Support\Collection<string, int> $statusCounts
     *  @return \Illuminate\Support\Collection<int, array{status: string, count: int, percent: float, color: string}>
     */
    private function statusSegments(\Illuminate\Support\Collection $statusCounts, int $totalProjects): \Illuminate\Support\Collection
    {
        $statusColors = [
            'planning' => '#f59e0b',
            'progress' => '#6366f1',
            'done' => '#10b981',
            'cancel' => '#f43f5e',
        ];
        $totalForChart = max(1, $totalProjects);

        return collect(self::STATUSES)->map(function (string $status) use ($statusCounts, $statusColors, $totalForChart): array {
            $count = (int) ($statusCounts[$status] ?? 0);

            return [
                'status' => $status,
                'count' => $count,
                'percent' => round(($count / $totalForChart) * 100, 1),
                'color' => $statusColors[$status],
            ];
        });
    }

    /** @return \Illuminate\Support\Collection<int, array{label: string, value: float, margin: float}> */
    private function monthlyFinancials(Builder $projectQuery): \Illuminate\Support\Collection
    {
        $trendStart = now()->startOfMonth()->subMonths(11);
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
        $monthlyTotals = (clone $projectQuery)
            ->where('created_at', '>=', $trendStart)
            ->reorder()
            ->selectRaw("{$monthExpression} as month, COALESCE(SUM(project_value), 0) as value, COALESCE(SUM(margin), 0) as margin")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        return collect(range(0, 11))->map(function (int $offset) use ($trendStart, $monthlyTotals): array {
            $month = $trendStart->copy()->addMonths($offset);
            $totals = $monthlyTotals->get($month->format('Y-m'));

            return [
                'label' => $month->translatedFormat('M')." '".$month->format('y'),
                'value' => (float) ($totals->value ?? 0),
                'margin' => (float) ($totals->margin ?? 0),
            ];
        });
    }
}
