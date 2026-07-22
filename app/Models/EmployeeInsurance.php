<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\EmployeeInsuranceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeInsurance extends Model
{
    /** @use HasFactory<EmployeeInsuranceFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    /**
     * @var list<string>
     */
    public const TYPES = ['medical', 'life', 'dental', 'other'];

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'provider',
        'policy_number',
        'type',
        'coverage_amount',
        'dependents_covered',
        'start_date',
        'end_date',
        'status',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'coverage_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
