<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'member_id', 'user_id', 'amount',
        'payment_date', 'method', 'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}