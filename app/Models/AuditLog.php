<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'module', 'description', 'record_type', 'record_id',
        'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /** Audit logs are append-only: block edits and deletes at the model level. */
    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit logs are read-only.'));
        static::deleting(fn () => throw new LogicException('Audit logs cannot be deleted.'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}