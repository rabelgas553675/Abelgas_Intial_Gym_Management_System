<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory, Auditable;

    public function auditModule(): string
    {
        return match (true) {
            str_starts_with($this->key, 'gym_')   => 'Gym Fees',
            str_starts_with($this->key, 'coach_') => 'Instructor Rates',
            default                               => 'Payment Settings',
        };
    }

    public function auditLabel(): string
    {
        if (preg_match('/^coach_instructor_(\d+)_(.+)$/', $this->key, $m)) {
            $name = User::query()->where('id', (int) $m[1])->value('name') ?? 'Instructor #' . $m[1];
            return "{$name} — {$m[2]} rate";
        }
        return str_replace('_', ' ', $this->key);
    }

    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }
}
