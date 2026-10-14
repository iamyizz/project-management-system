<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserProjectController extends Controller
{
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['planning', 'progress', 'done', 'cancel'])],
        ]);

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
            ->latest()
            ->paginate(10)
            ->withQueryString();

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
            'filters'
        ));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        abort_if((int) $project->pic_id !== (int) auth()->id(), 403);

        abort_if(in_array($project->status, ['done', 'cancel'], true), 403,
            'Project yang sudah selesai atau dibatalkan tidak bisa diubah.');

        $validated = $request->validate([
            'status' => ['required', Rule::in(['planning', 'progress', 'done'])],
        ]);

        $allowedNextStatuses = [
            'planning' => 'progress',
            'progress' => 'done',
        ];
        if ($validated['status'] !== $project->status
            && ($allowedNextStatuses[$project->status] ?? null) !== $validated['status']) {
            throw ValidationException::withMessages([
                'status' => 'Status project harus berjalan berurutan dari Planning ke Progress lalu Done.',
            ]);
        }

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
