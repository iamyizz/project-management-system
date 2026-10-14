<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserProjectController extends Controller
{
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['planning', 'progress', 'done', 'cancel'])],
            'deadline_scope' => ['nullable', Rule::in(['overdue', 'next_7', 'next_30', 'active', 'done'])],
        ]);

        $assignedProjects = Project::query()->where('pic_id', $userId);

        $projects = Project::with(['endUser:id,nama,industri,contact,telepon,email,kota', 'pic:id,nama', 'statusHistories', 'activities'])
            ->where('pic_id', $userId)
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $escapedSearch = ProjectService::escapeLikeWildcards($search);
                $query->where(function ($query) use ($escapedSearch): void {
                    $query->where('project_name', 'like', "%{$escapedSearch}%")
                        ->orWhereHas('endUser', fn ($query) => $query->where('nama', 'like', "%{$escapedSearch}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['deadline_scope'] ?? null, function ($query, string $scope): void {
                match ($scope) {
                    'overdue' => $query->whereNotIn('status', ['done', 'cancel'])
                        ->whereNotNull('deadline_date')
                        ->whereDate('deadline_date', '<', today()),
                    'next_7' => $query->whereNotIn('status', ['done', 'cancel'])
                        ->whereNotNull('deadline_date')
                        ->whereBetween('deadline_date', [today(), today()->copy()->addDays(7)]),
                    'next_30' => $query->whereNotIn('status', ['done', 'cancel'])
                        ->whereNotNull('deadline_date')
                        ->whereBetween('deadline_date', [today(), today()->copy()->addDays(30)]),
                    'active' => $query->whereIn('status', ['planning', 'progress']),
                    'done' => $query->where('status', 'done'),
                    default => null,
                };
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $actionableProjects = (clone $assignedProjects)->whereNotIn('status', ['done', 'cancel']);
        $deadlineCounts = [
            'all' => (clone $assignedProjects)->count(),
            'overdue' => (clone $actionableProjects)
                ->whereNotNull('deadline_date')
                ->whereDate('deadline_date', '<', today())
                ->count(),
            'next_7' => (clone $actionableProjects)
                ->whereNotNull('deadline_date')
                ->whereBetween('deadline_date', [today(), today()->copy()->addDays(7)])
                ->count(),
            'next_30' => (clone $actionableProjects)
                ->whereNotNull('deadline_date')
                ->whereBetween('deadline_date', [today(), today()->copy()->addDays(30)])
                ->count(),
            'active' => (clone $assignedProjects)->whereIn('status', ['planning', 'progress'])->count(),
            'done' => (clone $assignedProjects)->where('status', 'done')->count(),
        ];

        $stats = Project::where('pic_id', $userId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'planning') as planning,
                SUM(status = 'progress') as progress,
                SUM(status = 'done') as done
            ")
            ->first();

        $totalProject = (int) $stats->total;
        $active = (int) $stats->planning + (int) $stats->progress;
        $done = (int) $stats->done;

        return view('user.projects.index', compact(
            'projects',
            'totalProject',
            'active',
            'done',
            'filters',
            'deadlineCounts',
        ));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        abort_if((int) $project->pic_id !== (int) auth()->id(), 403);

        abort_if($project->status === 'cancel', 403,
            'Project yang sudah dibatalkan tidak bisa diubah.');

        $validated = $request->validate([
            'status' => ['required', Rule::in(['planning', 'progress', 'done'])],
        ]);

        DB::transaction(function () use ($project, $validated): void {
            $previousStatus = $project->status;
            $project->update(['status' => $validated['status']]);

            if ($previousStatus !== $project->status) {
                $project->statusHistories()->create([
                    'project_name' => $project->project_name,
                    'actor_id' => auth()->id(),
                    'actor_name' => auth()->user()->nama,
                    'from_status' => $previousStatus,
                    'to_status' => $project->status,
                ]);
            }
        });

        return back()->with('success', 'Progress project berhasil diperbarui');
    }
}
