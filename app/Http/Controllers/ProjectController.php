<?php

namespace App\Http\Controllers;

use App\Models\EndUser;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    private const STATUSES = ['planning', 'progress', 'done', 'cancel'];
    private const MAX_MONEY = 9999999999999.99;

    public function index()
    {
        $projects = Project::with(['endUser', 'pic'])
            ->latest()
            ->paginate(10);

        $endUsers = EndUser::orderBy('nama')->get(['id', 'nama']);

        $pics = User::where('is_active', true)
            ->orWhereIn('id', Project::select('pic_id'))
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return view('admin.projects.index', compact('projects', 'endUsers', 'pics'));
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
        $account = (float) ($data['account'] ?? 0);
        $quotation = (float) ($data['quotation_distribusi'] ?? 0);
        $margin = $account - $quotation;
        $percentage = $account > 0 ? ($margin / $account) * 100 : 0;

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
                Rule::exists('users', 'id')->where(fn($q) => $q->where('is_active', true)),
            ],
            'project_name' => ['required', 'string', 'max:255'],
            'account' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:' . self::MAX_MONEY],
            'po_number' => ['nullable', 'string', 'max:255'],
            'quotation_number' => ['nullable', 'string', 'max:255'],
            'quotation_distribusi' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:' . self::MAX_MONEY],
            'status' => ['required', Rule::in(self::STATUSES)],
            'deadline_date' => ['nullable', 'date'],
        ];
    }
}
