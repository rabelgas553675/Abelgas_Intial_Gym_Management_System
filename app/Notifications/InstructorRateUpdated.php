<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InstructorRateUpdated extends Notification
{
    use Queueable;

    public function __construct(public array $changes = [])
    {
        //
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'   => 'Coaching rate update',
            'message' => $this->buildMessage(),
            'changes' => $this->changes,
        ];
    }

    protected function buildMessage(): string
    {
        if (empty($this->changes)) {
            return 'Your coaching rate has been updated.';
        }

        $parts = [];

        foreach ($this->changes as $plan => $change) {
            $old = (int) ($change['from'] ?? 0);
            $new = (int) ($change['to'] ?? 0);
            $direction = $new > $old ? 'increased' : 'decreased';
            $difference = abs($new - $old);
            $parts[] = sprintf('%s %s by ₱%s (₱%s → ₱%s)', $plan, $direction, number_format($difference), number_format($old), number_format($new));
        }

        return 'Your coaching rate was updated: ' . implode('; ', $parts) . '.';
    }
}
