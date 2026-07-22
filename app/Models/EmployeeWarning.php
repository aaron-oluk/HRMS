<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\EmployeeWarningFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWarning extends Model
{
    /** @use HasFactory<EmployeeWarningFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    /**
     * @var list<string>
     */
    public const SEVERITIES = ['verbal', 'written', 'final'];

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'severity',
        'reason',
        'issued_by',
        'issued_at',
        'acknowledged_at',
        'expires_at',
        'status',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'acknowledged_at' => 'datetime',
            'expires_at' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
