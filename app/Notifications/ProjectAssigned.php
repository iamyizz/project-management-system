<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectAssigned extends Notification
{
    use Queueable;

    public function __construct(private readonly array $projectDetails)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deadline = $this->projectDetails['deadline']
            ? \Illuminate\Support\Carbon::parse($this->projectDetails['deadline'])->translatedFormat('d M Y')
            : 'Belum ditentukan';

        return (new MailMessage)
            ->subject('Project baru ditugaskan kepada kamu')
            ->greeting("Halo {$notifiable->nama},")
            ->line("Kamu ditunjuk sebagai PIC untuk project {$this->projectDetails['project_name']}.")
            ->line("End user: {$this->projectDetails['end_user_name']}")
            ->line("Deadline: {$deadline}")
            ->action('Lihat Project', route('user.projects.index'))
            ->line("Project ditugaskan oleh {$this->projectDetails['assigned_by']}.");
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(object $notifiable): array
    {
        return [
            ...$this->projectDetails,
            'message' => 'Kamu ditunjuk sebagai PIC project baru.',
            'url' => route('user.projects.index'),
        ];
    }
}
