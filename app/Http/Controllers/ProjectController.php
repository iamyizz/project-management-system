<?php

namespace App\Http\Controllers;

use App\Models\EndUser;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    private const STATUSES = ['planning', 'progress', 'done', 'cancel'];

    private const MAX_MONEY = 9999999999999.99;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'pic_id' => ['nullable', 'integer', 'exists:users,id'],
            'end_user_id' => ['nullable', 'integer', 'exists:end_users,id'],
        ]);

        $projects = Project::with(['endUser', 'pic'])
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('project_name', 'like', "%{$search}%")
                        ->orWhere('po_number', 'like', "%{$search}%")
                        ->orWhere('quotation_number', 'like', "%{$search}%")
                        ->orWhereHas('endUser', fn ($query) => $query->where('nama', 'like', "%{$search}%"))
                        ->orWhereHas('pic', fn ($query) => $query->where('nama', 'like', "%{$search}%"));
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

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated = $this->calculateFinance($validated);

        Project::create($validated);

        return back()->with('success', 'Project berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate($this->rules($project));
        $validated = $this->calculateFinance($validated);

        $project->update($validated);

        return back()->with('success', 'Project berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return back()->with('success', 'Project berhasil dihapus.');
    }

    private function calculateFinance(array $data): array
    {
        $projectValue = (float) ($data['project_value'] ?? 0);
        $internalCost = (float) ($data['internal_cost'] ?? 0);
        $margin = $projectValue - $internalCost;
        $percentage = $projectValue > 0 ? ($margin / $projectValue) * 100 : 0;

        return array_merge($data, [
            'margin' => $margin,
            'percentage' => $percentage,
        ]);
    }

    private function rules(?Project $project = null): array
    {
        return [
            'end_user_id' => ['required', 'exists:end_users,id'],
            'pic_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) use ($project): void {
                    $query->where('is_active', true);

                    if ($project?->pic_id) {
                        $query->orWhere('id', $project->pic_id);
                    }
                }),
            ],
            'project_name' => ['required', 'string', 'max:255'],
            'project_value' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:'.self::MAX_MONEY],
            'po_number' => ['nullable', 'string', 'max:255'],
            'quotation_number' => ['nullable', 'string', 'max:255'],
            'internal_cost' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:'.self::MAX_MONEY],
            'status' => ['required', Rule::in(self::STATUSES)],
            'deadline_date' => ['nullable', 'date'],
        ];
    }
}
