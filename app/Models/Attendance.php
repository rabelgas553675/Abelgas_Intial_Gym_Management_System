<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'member_id',
        'staff_user_id', // MUST BE HERE
        'time_in',
        'time_out',
        'date',
        'duration_minutes',
        'entry_method',
        'scanned_by'
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // This allows the controller to get the Staff Name
    public function user()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    // This handles the "1h 20m" display logic
    public function getDurationFormattedAttribute()
    {
        if (!$this->duration_minutes) return '—';
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;
        return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
    }
}