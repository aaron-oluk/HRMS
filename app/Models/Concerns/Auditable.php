<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Context;

trait Auditable
{
    protected static array $auditExcluded = [
        'id', 'tenant_id', 'created_at', 'updated_at', 'created_by', 'updated_by',
    ];

    public static function bootAuditable(): void
    {
        static::created(function ($model): void {
            $attributes = collect($model->getAttributes())
                ->except(static::auditExcludedAttributes())
                ->all();

            foreach ($attributes as $field => $value) {
                static::writeAuditLog($model, 'created', $field, null, $value);
            }
        });

        static::updated(function ($model): void {
            $dirty = collect($model->getChanges())
                ->except(static::auditExcludedAttributes())
                ->all();

            foreach ($dirty as $field => $newValue) {
                static::writeAuditLog($model, 'updated', $field, $model->getOriginal($field), $newValue);
            }
        });

        static::deleted(function ($model): void {
            static::writeAuditLog($model, 'deleted', null, null, null);
        });
    }

    protected static function auditExcludedAttributes(): array
    {
        return static::$auditExcluded;
    }

    protected static function writeAuditLog($model, string $action, ?string $field, mixed $old, mixed $new): void
    {
        AuditLog::create([
            'tenant_id' => $model->tenant_id ?? null,
            'actor_id' => auth()->id(),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'field' => $field,
            'old_value' => static::stringifyAuditValue($old),
            'new_value' => static::stringifyAuditValue($new),
            'request_id' => Context::get('request_id'),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    protected static function stringifyAuditValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? (string) $value : json_encode($value);
    }
}
