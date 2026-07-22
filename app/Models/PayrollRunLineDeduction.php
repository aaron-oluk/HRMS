<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PayrollRunLineDeductionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PayrollRunLineDeduction extends Model
{
    /** @use HasFactory<PayrollRunLineDeductionFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'payroll_run_line_id',
        'source_type',
        'source_id',
        'label',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function payrollRunLine(): BelongsTo
    {
        return $this->belongsTo(PayrollRunLine::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
