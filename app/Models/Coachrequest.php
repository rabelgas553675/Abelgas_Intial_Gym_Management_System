<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoachRequest extends Model
{
    protected $fillable = [
        'member_id',
        'instructor_id',
        'status',
        'message',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}