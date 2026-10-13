<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
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
        ]);

        $projects = Project::with('endUser')
            ->where('pic_id', $userId)
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('project_name', 'like', "%{$search}%")
                        ->orWhereHas('endUser', fn ($query) => $query->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = Project::where('pic_id', $userId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'progress') as progress,
                SUM(status = 'done') as done
            ")
            ->first();

        $totalProject = (int) $stats->total;
        $progress = (int) $stats->progress;
        $done = (int) $stats->done;

        return view('user.projects.index', compact(
            'projects',
            'totalProject',
            'progress',
            'done',
            'filters'
        ));
    }

    public function update(Request $request, Project $project)
    {
        abort_if((int) $project->pic_id !== (int) auth()->id(), 403);

        abort_if(
            in_array($project->status, ['done', 'cancel'], true),
            403,
            'Project yang sudah selesai atau dibatalkan tidak bisa diubah.'
        );

        $request->validate([
            'status' => 'required|in:planning,progress,done',
        ]);

        $project->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Progress project berhasil diperbarui');
    }
}
