<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'member_id', 'time_in', 'time_out', 'date',
        'duration_minutes', 'scanned_by', 'entry_method',
    ];

    protected $casts = [
        'time_in'  => 'datetime',
        'time_out' => 'datetime',
        'date'     => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function getDurationFormattedAttribute(): string
    {
        if ($this->duration_minutes === null) return '—';
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;
        return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
    }
}