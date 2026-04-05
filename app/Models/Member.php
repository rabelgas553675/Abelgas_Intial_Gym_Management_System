<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'name',           // single name field — matches your controller
        'email',
        'phone',
        'membership_type',
        'start_date',
        'end_date',
        'fee',
        'status',
        'qr_code',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isInsideNow(): bool
    {
        return $this->attendance()
            ->whereNull('time_out')
            ->whereDate('time_in', today())
            ->exists();
    }
}