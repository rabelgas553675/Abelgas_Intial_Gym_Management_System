<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A member's request to switch fitness plan. It only becomes the member's real
 * plan after the assigned coach approves it (see App\Services\PlanChangeService).
 */
class PlanChangeRequest extends Model
{
    public const PENDING   = 'Pending';
    public const APPROVED  = 'Approved';
    public const REJECTED  = 'Rejected';
    public const CANCELLED = 'Cancelled';

    public const PLANS = [
        'Calisthenics',
        'Bodybuilding',
        'Plyometrics',
        'Powerlifting',
        'Endurance',
        'Functional Training',
        'Hybrid Training',
    ];

    protected $fillable = [
        'member_id',
        'instructor_id',
        'current_plan',
        'requested_plan',
        'reason',
        'status',
        'pending_member_id',
        'coach_feedback',
        'reviewed_by',
        'requested_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'reviewed_at'  => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ── State helpers ─────────────────────────────────────────────────────────
    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::REJECTED;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopePending($query)
    {
        return $query->where('status', self::PENDING);
    }
}