<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A Walk-In / Day Pass sale.
 *
 * Deliberately has NO relation to Member or Payment: a walk-in customer is not
 * a member, and a day pass never touches a membership.
 */
class WalkInPayment extends Model
{
    use Auditable;

    public const PASS_TYPE = 'Day Pass';

    public const METHODS = ['Cash', 'GCash', 'Bank Transfer', 'Card'];

    protected $fillable = [
        'receipt_number', 'customer_name', 'contact_number', 'pass_type',
        'day_pass_rate', 'amount', 'method', 'payment_date', 'status',
        'notes', 'processed_by',
    ];

    protected $casts = [
        'payment_date'  => 'date',
        'day_pass_rate' => 'decimal:2',
        'amount'        => 'decimal:2',
    ];

    // ── Auditable hooks ───────────────────────────────────────────────────────
    protected string $auditModule = 'Walk-In Payments';

    public function auditAction(string $event, ?array $old, ?array $new): string
    {
        return match ($event) { 'created' => 'recorded', 'deleted' => 'deleted', default => 'updated' };
    }

    public function auditLabel(): string
    {
        return $this->receipt_number . ' — ' . $this->customer_name
            . ' (₱' . number_format((float) $this->amount, 2) . ')';
    }

    // ── Relations / scopes ────────────────────────────────────────────────────
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                     ->whereYear('payment_date', now()->year);
    }

    public static function generateReceiptNumber(): string
    {
        do {
            $number = 'WLK-' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 10));
        } while (static::where('receipt_number', $number)->exists());

        return $number;
    }
}