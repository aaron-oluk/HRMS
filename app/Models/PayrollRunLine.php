<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\PayrollRunLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRunLine extends Model
{
    /** @use HasFactory<PayrollRunLineFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'payroll_run_id',
        'employee_id',
        'employment_id',
        'basic_salary',
        'gross_pay',
        'paye_amount',
        'nssf_employee_amount',
        'nssf_employer_amount',
        'other_deductions',
        'net_pay',
        'currency',
    ];

    protected $attributes = [
        'other_deductions' => 0,
        'currency' => 'UGX',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'gross_pay' => 'decimal:2',
            'paye_amount' => 'decimal:2',
            'nssf_employee_amount' => 'decimal:2',
            'nssf_employer_amount' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'net_pay' => 'decimal:2',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employment(): BelongsTo
    {
        return $this->belongsTo(Employment::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(PayrollRunLineDeduction::class);
    }
}
