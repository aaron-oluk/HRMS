<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\ClockEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class ClockEvent extends Model
{
    /** @use HasFactory<ClockEventFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'type',
        'occurred_at',
        'source',
        'latitude',
        'longitude',
        'idempotency_key',
    ];

    protected $attributes = [
        'source' => 'web',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        throw new LogicException('Clock events are immutable and cannot be updated.');
    }

    public function delete(): bool
    {
        throw new LogicException('Clock events are immutable and cannot be deleted.');
    }
}
