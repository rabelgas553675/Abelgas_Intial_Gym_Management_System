<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'member_id', 'processed_by', 'receipt_number',
        'fitness_plan', 'membership_type',
        'amount', 'payment_date', 'method', 'status', 'notes',
    ];

  protected $casts = [
    'payment_date' => 'date',
];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public static function generateReceiptNumber(): string
    {
        return 'RCP-' . strtoupper(uniqid());
    }
}