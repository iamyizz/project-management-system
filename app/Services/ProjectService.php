<?php

namespace App\Services;

use App\Models\Project;
use App\Notifications\ProjectAssigned;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * Calculate margin and percentage from project value and internal cost.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function calculateFinance(array $data): array
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

    public function createProject(array $validated): Project
    {
        $validated = $this->calculateFinance($validated);

        return DB::transaction(function () use ($validated): Project {
            $project = Project::create($validated);
            $project->margin = $validated['margin'];
            $project->percentage = $validated['percentage'];
            $project->saveQuietly();

            $this->recordStatusChange($project, null, $project->status);

            return $project;
        });
    }

    public function updateProject(Project $project, array $validated): Project
    {
        $validated = $this->calculateFinance($validated);

        DB::transaction(function () use ($project, $validated): void {
            $previousStatus = $project->status;
            $previousValues = $this->activityValues($project);

            $project->update($validated);
            $project->margin = $validated['margin'];
            $project->percentage = $validated['percentage'];
            $project->saveQuietly();

            $this->recordProjectActivities($project, $previousValues);

            if ($previousStatus !== $project->status) {
                $this->recordStatusChange($project, $previousStatus, $project->status);
            }
        });

        return $project;
    }

    public function notifyPicOfAssignment(Project $project): void
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

    public function recordStatusChange(Project $project, ?string $fromStatus, string $toStatus): void
    {
        $project->statusHistories()->create([
            'project_name' => $project->project_name,
            'actor_id' => auth()->id(),
            'actor_name' => auth()->user()->nama,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
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

    /** Escape SQL LIKE wildcards so search input is matched literally. */
    public static function escapeLikeWildcards(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
