<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstructorFee extends Model
{
    protected $fillable = [
        'instructor_id',
        'member_id',
        'amount',
        'payment_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}