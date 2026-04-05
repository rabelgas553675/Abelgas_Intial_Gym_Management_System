<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['member_id', 'time_in', 'time_out'];

    protected $casts = [
        'time_in'  => 'datetime',
        'time_out' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}