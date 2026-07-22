<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\EmployeeDeductionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDeduction extends Model
{
    /** @use HasFactory<EmployeeDeductionFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    /**
     * @var list<string>
     */
    public const FREQUENCIES = ['one_time', 'recurring'];

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'label',
        'amount',
        'frequency',
        'status',
        'effective_date',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
