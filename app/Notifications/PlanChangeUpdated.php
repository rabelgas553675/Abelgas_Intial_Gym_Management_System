<?php

namespace App\Notifications;

use App\Models\PlanChangeRequest;
use Illuminate\Notifications\Notification;

/**
 * Stored in the existing `notifications` table (database channel).
 *
 * event: submitted (to the member) | requested (to the coach)
 *        approved | rejected (to the member) | cancelled (to the coach)
 */
class PlanChangeUpdated extends Notification
{
    public function __construct(
        public PlanChangeRequest $request,
        public string $event,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $r = $this->request;

        return [
            'title'                   => $this->title(),
            'message'                 => $this->message(),
            'event'                   => $this->event,
            'plan_change_request_id'  => $r->id,
            'current_plan'            => $r->current_plan,
            'requested_plan'          => $r->requested_plan,
        ];
    }

    private function title(): string
    {
        return match ($this->event) {
            'submitted' => 'Plan change request submitted',
            'requested' => 'New plan change request',
            'approved'  => 'Plan change approved',
            'rejected'  => 'Plan change rejected',
            'cancelled' => 'Plan change request cancelled',
            default     => 'Plan change update',
        };
    }

    private function message(): string
    {
        $r      = $this->request;
        $member = $r->member?->full_name ?? 'A member';

        return match ($this->event) {
            'submitted' => "Your request to change your fitness plan to {$r->requested_plan} has been submitted to your coach for approval. Your current plan remains {$r->current_plan} until your request is approved.",
            'requested' => "{$member} asked to change from {$r->current_plan} to {$r->requested_plan}.",
            'approved'  => "Your coach approved your request. Your fitness plan is now {$r->requested_plan}.",
            'rejected'  => "Your coach declined your request to change to {$r->requested_plan}. Your plan stays {$r->current_plan}."
                           . ($r->coach_feedback ? " Reason: {$r->coach_feedback}" : ''),
            'cancelled' => "{$member} cancelled the request to change from {$r->current_plan} to {$r->requested_plan}.",
            default     => 'Your plan change request was updated.',
        };
    }
}