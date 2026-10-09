<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use Auditable;

    /** Maximum length of one session. */
    const MAX_HOURS = 12;

    protected string $auditModule = 'Attendance';

    public function auditExcept(): array
    {
        return ['scanned_by']; // holds the staff member's QR token
    }

    public function auditLabel(): string
    {
        return $this->member?->name ?? $this->user?->name ?? 'Unknown';
    }

    public function auditAction(string $event, ?array $old, ?array $new): string
    {
        if ($event === 'created') return 'check_in';

        if ($event === 'updated' && array_key_exists('time_out', $new ?? [])) {
            return ($new['auto_timed_out'] ?? false) ? 'auto_check_out' : 'check_out';
        }

        return $event;
    }

    protected $fillable = [
        'member_id',
        'staff_user_id',
        'time_in',
        'time_out',
        'auto_timed_out',
        'date',
        'duration_minutes',
        'entry_method',
        'scanned_by',
    ];

    protected $casts = [
        'time_in'        => 'datetime',
        'time_out'       => 'datetime',
        'date'           => 'date',
        'auto_timed_out' => 'boolean',
    ];

    /* ─────────────── 12-hour rule ─────────────── */

    protected static function booted(): void
    {
        // Runs on every Eloquent save (QR scan, manual entry, Time Out button, auto-close):
        // caps the session at 12h and keeps duration_minutes correct.
        static::saving(function (self $a) {
            if (!$a->time_in || !$a->time_out) {
                return;
            }

            $limit = $a->time_in->copy()->addHours(self::MAX_HOURS);

            if ($a->time_out->gt($limit)) {
                $a->time_out       = $limit;
                $a->auto_timed_out = true;
            }

            $a->duration_minutes = max(
                0,
                intdiv($a->time_out->timestamp - $a->time_in->timestamp, 60)
            );
        });
    }

    /** Open records that have been inside for 12h or more. */
    public function scopeOverdue(Builder $q): Builder
    {
        return $q->whereNull('time_out')
                 ->where('time_in', '<=', now()->subHours(self::MAX_HOURS));
    }

    /** Time out every overdue record. Returns how many were closed. */
    public static function closeOverdue(): int
    {
        $closed = 0;

        static::query()->overdue()->chunkById(200, function ($rows) use (&$closed) {
            foreach ($rows as $row) {
                // Rehydrate as an Eloquent model in case the chunk returns raw rows.
                $a = static::findOrFail($row->id);

                // Exactly time_in + 12h, not "now", so late cron runs don't inflate the time
                $a->time_out       = $a->time_in->copy()->addHours(self::MAX_HOURS);
                $a->auto_timed_out = true;
                $a->save(); // saving hook sets duration_minutes
                $closed++;
            }
        });

        return $closed;
    }

    /* ─────────────── Relations ─────────────── */

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    /* ─────────────── Accessors ─────────────── */

    // "1h 20m" display
    public function getDurationFormattedAttribute()
    {
        if ($this->duration_minutes === null) return '—';
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;
        return $h > 0 ? "{$h}h {$m}m" : "{$m}m";
    }
}