<?php

namespace App\Traits;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Add `use Auditable;` to a model to log create/update/delete automatically.
 *
 * Optional overrides on the model:
 *   protected string $auditModule = 'Members';        // or override auditModule()
 *   public function auditLabel(): string               // human label of the record
 *   public function auditExcept(): array               // attributes never logged
 *   public function auditAction(string $event, ?array $old, ?array $new): string
 *
 * @mixin Model
 *
 * Editor hints only (Intelephense can't see these Eloquent static methods through a trait):
 * @method static void created(\Closure|callable|array|string $callback)
 * @method static void updated(\Closure|callable|array|string $callback)
 * @method static void deleted(\Closure|callable|array|string $callback)
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($m) => AuditLogger::record($m, 'created'));
        static::updated(fn ($m) => AuditLogger::record($m, 'updated'));
        static::deleted(fn ($m) => AuditLogger::record($m, 'deleted'));
    }

    public function auditModule(): string
    {
        return property_exists($this, 'auditModule')
            ? $this->auditModule
            : str(class_basename($this))->plural()->headline()->toString();
    }

    public function auditLabel(): string
    {
        return $this->name ?? ('#' . $this->getKey());
    }

    public function auditExcept(): array
    {
        return [];
    }

    public function auditAction(string $event, ?array $old, ?array $new): string
    {
        return $event; // created | updated | deleted
    }
}