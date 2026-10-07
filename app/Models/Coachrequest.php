<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * NOTE: if you already have a CoachRequest model, merge the fillable/casts,
 * the extra relations and the helper methods into it instead of replacing it.
 */
class CoachRequest extends Model
{
    protected $fillable = [
        'member_id',
        'instructor_id',
        'status',            // pending | approved | rejected
        'source',            // member | manual
        'message',
        'coach_membership_type',
        'payment_id',
        'requested_by',
        'starts_on',
        'responded_at',
        'activated_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'starts_on'    => 'date',
            'responded_at' => 'datetime',
            'activated_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * The held coach payment. Payment hides "Awaiting Coach" / "Rejected" rows with a
     * global scope, so it has to be lifted here or this relation would always be empty.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class)->withoutGlobalScope('confirmed');
    }

    // ── State helpers ─────────────────────────────────────────────────────────

    /** Recorded by admin/staff (has a held payment) rather than by the member. */
    public function isManual(): bool
    {
        return $this->source === 'manual';
    }

    /** Approved, but the coaching starts on a future date and is not active yet. */
    public function isScheduled(): bool
    {
        return $this->status === 'approved'
            && $this->activated_at === null
            && $this->starts_on !== null
            && $this->starts_on->copy()->startOfDay()->gt(today());
    }

    /**
     * Close every open request of a member (used when a newer request replaces them).
     * Any held payment attached to them is rejected so it can never count as income.
     */
    public static function supersedePending(int|string $memberId): int
    {
        return DB::transaction(function () use ($memberId) {
            $pending = static::query()
                ->where('member_id', $memberId)
                ->where('status', 'pending')
                ->get();

            foreach ($pending as $request) {
                if ($request->payment_id) {
                    Payment::withUnconfirmed()
                        ->whereKey($request->payment_id)
                        ->where('status', Payment::STATUS_AWAITING_COACH)
                        ->update(['status' => Payment::STATUS_REJECTED]);
                }

                $request->forceFill([
                    'status'           => 'rejected',
                    'responded_at'     => now(),
                    'rejection_reason' => 'Superseded by a newer request.',
                ])->save();
            }

            return $pending->count();
        });
    }
}