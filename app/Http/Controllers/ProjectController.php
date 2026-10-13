<?php

namespace App\Http\Controllers;

use App\Models\EndUser;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
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

        $projects = Project::with(['endUser', 'pic', 'statusHistories', 'activities'])
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
        $validated = $request->validate([
            ...$this->rules(),
            'submission_token' => ['required', 'uuid'],
        ]);
        $validated = $this->calculateFinance($validated);

        try {
            $project = DB::transaction(function () use ($validated): Project {
                $project = Project::create($validated);
                $this->recordStatusChange($project, null, $project->status);

                return $project;
            });
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
        $this->notifyPicOfAssignment($project);

        return back()->with('success', 'Project berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate($this->rules($project));
        $validated = $this->calculateFinance($validated);

        $previousPicId = $project->pic_id;

        DB::transaction(function () use ($project, $validated): void {
            $previousStatus = $project->status;
            $previousValues = $this->activityValues($project);
            $project->update($validated);

            $this->recordProjectActivities($project, $previousValues);

            if ($previousStatus !== $project->status) {
                $this->recordStatusChange($project, $previousStatus, $project->status);
            }
        });

        if ((int) $previousPicId !== (int) $project->pic_id) {
            $project->load(['pic', 'endUser']);
            $this->notifyPicOfAssignment($project);
        }

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

    private function recordStatusChange(Project $project, ?string $fromStatus, string $toStatus): void
    {
        $project->statusHistories()->create([
            'project_name' => $project->project_name,
            'actor_id' => auth()->id(),
            'actor_name' => auth()->user()->nama,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ]);
    }

    private function notifyPicOfAssignment(Project $project): void
    {
        if (! $project->pic?->is_active || ! $project->pic->email) {
            return;
        }

        $project->pic->notify(new ProjectAssigned([
            'project_id' => $project->id,
            'project_name' => $project->project_name,
            'end_user_name' => $project->endUser?->nama ?? '-',
            'deadline' => $project->deadline_date?->toDateString(),
            'assigned_by' => auth()->user()->nama,
        ]));
    }

    private function activityValues(Project $project): array
    {
        return [
            'project_name' => $project->project_name,
            'end_user' => $project->endUser?->nama,
            'pic' => $project->pic?->nama,
            'project_value' => $project->project_value,
            'internal_cost' => $project->internal_cost,
            'po_number' => $project->po_number,
            'quotation_number' => $project->quotation_number,
            'deadline_date' => $project->deadline_date?->toDateString(),
        ];
    }

    private function recordProjectActivities(Project $project, array $previousValues): void
    {
        $project->load(['endUser', 'pic']);
        $currentValues = $this->activityValues($project);
        $labels = [
            'project_name' => 'Nama Project',
            'end_user' => 'End User',
            'pic' => 'PIC',
            'project_value' => 'Project Value',
            'internal_cost' => 'Internal Cost',
            'po_number' => 'Nomor PO',
            'quotation_number' => 'Nomor Quotation',
            'deadline_date' => 'Deadline',
        ];

        foreach ($labels as $field => $label) {
            $oldValue = $previousValues[$field] ?? null;
            $newValue = $currentValues[$field] ?? null;

            if ((string) ($oldValue ?? '') === (string) ($newValue ?? '')) {
                continue;
            }

            $project->activities()->create([
                'project_name' => $project->project_name,
                'actor_id' => auth()->id(),
                'actor_name' => auth()->user()->nama,
                'field' => $label,
                'old_value' => $this->formatActivityValue($field, $oldValue),
                'new_value' => $this->formatActivityValue($field, $newValue),
            ]);
        }
    }

    private function formatActivityValue(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (in_array($field, ['project_value', 'internal_cost'], true)) {
            return 'Rp '.number_format((float) $value, 0, ',', '.');
        }

        if ($field === 'deadline_date') {
            return \Illuminate\Support\Carbon::parse($value)->translatedFormat('d M Y');
        }

        return (string) $value;
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
