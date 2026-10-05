<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AuditLogger
{
    /** Never store these attributes, on any model. */
    protected const ALWAYS_SKIP = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
        'created_at', 'updated_at',
    ];

    /** Generic entry (login, logout, failed login, custom events). */
    public static function log(
        string $action,
        string $module,
        ?string $description = null,
        ?Model $record = null,
        ?array $old = null,
        ?array $new = null,
        ?int $userId = null,
    ): void {
        try {
            AuditLog::create([
                'user_id'     => $userId ?? Auth::id(),
                'action'      => $action,
                'module'      => $module,
                'description' => $description,
                'record_type' => $record ? get_class($record) : null,
                'record_id'   => $record?->getKey(),
                'old_values'  => $old ?: null,
                'new_values'  => $new ?: null,
                'ip_address'  => request()?->ip(),
                'user_agent'  => request()?->userAgent(),
            ]);
        } catch (Throwable $e) {
            report($e); // auditing must never break the real request
        }
    }

    /** Model lifecycle entry, called by the Auditable trait. */
    public static function record(Model $model, string $event): void
    {
        // Skip seeders / artisan noise when nobody is logged in.
        if (app()->runningInConsole() && ! Auth::check()) {
            return;
        }

        $skip  = array_merge(self::ALWAYS_SKIP, $model->getHidden(), $model->auditExcept());
        $strip = fn (array $a) => array_diff_key($a, array_flip($skip));

        $old = $new = null;

        if ($event === 'created') {
            $new = $strip($model->getAttributes());
        } elseif ($event === 'deleted') {
            $old = $strip($model->getAttributes());
        } else { // updated
            $raw = $model->getChanges();

            // Log that a password changed, but never its value.
            if (array_key_exists('password', $raw)) {
                self::log('password_changed', $model->auditModule(), 'Password changed: ' . $model->auditLabel(), $model);
            }

            $changes = $strip($raw);
            if (! $changes) {
                return;
            }

            $old = array_intersect_key($strip($model->getRawOriginal()), $changes);
            $new = $changes;

            if (array_key_exists('role', $changes) || array_key_exists('role_id', $changes)) {
                self::log('role_changed', $model->auditModule(), 'Role changed: ' . $model->auditLabel(), $model, $old, $new);
            }
        }

        $action = $model->auditAction($event, $old, $new);

        self::log(
            $action,
            $model->auditModule(),
            ucfirst(str_replace('_', ' ', $action)) . ': ' . $model->auditLabel(),
            $model,
            $old,
            $new,
        );
    }
}