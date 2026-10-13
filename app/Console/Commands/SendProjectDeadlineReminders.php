<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Notifications\ProjectDeadlineReminder;
use Illuminate\Console\Command;

class SendProjectDeadlineReminders extends Command
{
    protected $signature = 'projects:send-deadline-reminders {--project= : Batasi pengingat ke satu ID project}';

    protected $description = 'Kirim pengingat deadline project ke PIC';

    public function handle(): int
    {
        $projectId = $this->option('project');

        if ($projectId !== null && ! ctype_digit($projectId)) {
            $this->error('Opsi --project harus berisi ID project berupa angka.');

            return self::FAILURE;
        }

        $today = today();
        $upcomingDate = $today->copy()->addDays(7);
        $sent = 0;

        Project::query()
            ->with(['pic', 'endUser'])
            ->when($projectId, fn ($query, string $id) => $query->whereKey($id))
            ->whereNotNull('deadline_date')
            ->whereNotIn('status', ['done', 'cancel'])
            ->whereBetween('deadline_date', [$today->toDateString(), $upcomingDate->toDateString()])
            ->orderBy('id')
            ->chunkById(100, function ($projects) use ($today, &$sent): void {
                foreach ($projects as $project) {
                    if (!$project->pic || !$project->pic->is_active) {
                        continue;
                    }

                    $stage = match (true) {
                        $project->deadline_date->isSameDay($today) => 'today',
                        default => 'upcoming',
                    };

                    $dedupeKey = implode(':', [$project->id, $stage, $project->deadline_date->toDateString()]);
                    $alreadySent = $project->pic->notifications()
                        ->where('type', ProjectDeadlineReminder::class)
                        ->whereJsonContains('data->dedupe_key', $dedupeKey)
                        ->exists();

                    if ($alreadySent) {
                        continue;
                    }

                    $project->pic->notify(new ProjectDeadlineReminder([
                        'project_id' => $project->id,
                        'project_name' => $project->project_name,
                        'end_user_name' => $project->endUser?->nama ?? '-',
                        'deadline' => $project->deadline_date->toDateString(),
                        'days_remaining' => $today->diffInDays($project->deadline_date),
                        'stage' => $stage,
                        'dedupe_key' => $dedupeKey,
                    ]));
                    $sent++;
                }
            });

        $this->info("{$sent} pengingat deadline dikirim.");

        return self::SUCCESS;
    }
}
