<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectDeadlineReminder extends Notification
{
    use Queueable;

    public function __construct(private readonly array $reminder)
    {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(object $notifiable): array
    {
        $message = match ($this->reminder['stage']) {
            'upcoming' => 'Deadline project tinggal '.($this->reminder['days_remaining'] ?? 7).' hari lagi.',
            default => 'Deadline project jatuh hari ini.',
        };

        return [
            ...$this->reminder,
            'message' => $message,
            'url' => route('user.projects.index'),
        ];
    }
}
