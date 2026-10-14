<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\EndUser;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    private const STATUSES = ['planning', 'progress', 'done', 'cancel'];

    public function __construct(private readonly ProjectService $projectService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'pic_id' => ['nullable', 'integer', 'exists:users,id'],
            'end_user_id' => ['nullable', 'integer', 'exists:end_users,id'],
        ]);

        $projects = Project::with(['endUser', 'pic', 'statusHistories', 'activities'])
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $escaped = ProjectService::escapeLikeWildcards($search);
                $query->where(function ($query) use ($escaped): void {
                    $query->where('project_name', 'like', "%{$escaped}%")
                        ->orWhere('po_number', 'like', "%{$escaped}%")
                        ->orWhere('quotation_number', 'like', "%{$escaped}%")
                        ->orWhereHas('endUser', fn ($query) => $query->where('nama', 'like', "%{$escaped}%"))
                        ->orWhereHas('pic', fn ($query) => $query->where('nama', 'like', "%{$escaped}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['pic_id'] ?? null, fn ($query, $picId) => $query->where('pic_id', (int) $picId))
            ->when($filters['end_user_id'] ?? null, fn ($query, $endUserId) => $query->where('end_user_id', (int) $endUserId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $endUsers = EndUser::orderBy('nama')->get(['id', 'nama']);

        $pics = User::where('is_active', true)
            ->orWhereIn('id', Project::select('pic_id'))
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return view('admin.projects.index', compact('projects', 'endUsers', 'pics', 'filters'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $project = $this->projectService->createProject($validated);
        } catch (QueryException $exception) {
            $alreadyCreated = Project::query()
                ->where('submission_token', $validated['submission_token'])
                ->exists();

            if (! $alreadyCreated) {
                throw $exception;
            }

            return redirect()->route('admin.projects.index')
                ->with('success', 'Project ini sudah tersimpan; pengiriman ulang tidak membuat duplikat.');
        }

        $project->load(['pic', 'endUser']);
        $this->projectService->notifyPicOfAssignment($project);

        return back()->with('success', 'Project berhasil ditambahkan.');
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();
        $previousPicId = $project->pic_id;

        $this->projectService->updateProject($project, $validated);

        if ((int) $previousPicId !== (int) $project->pic_id) {
            $project->load(['pic', 'endUser']);
            $this->projectService->notifyPicOfAssignment($project);
        }

        return back()->with('success', 'Project berhasil diperbarui.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return back()->with('success', 'Project berhasil dihapus.');
    }
}
