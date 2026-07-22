<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\StatutorySettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutorySetting extends Model
{
    /** @use HasFactory<StatutorySettingFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'paye_surcharge_floor',
        'paye_surcharge_rate',
        'nssf_employee_rate',
        'nssf_employer_rate',
    ];

    protected function casts(): array
    {
        return [
            'paye_surcharge_floor' => 'decimal:2',
            'paye_surcharge_rate' => 'decimal:4',
            'nssf_employee_rate' => 'decimal:4',
            'nssf_employer_rate' => 'decimal:4',
        ];
    }

    /**
     * One row per tenant (unique tenant_id) — the tenant scope on BelongsToTenant means this
     * always resolves the current tenant's row without needing an explicit where().
     */
    public static function current(): self
    {
        return static::query()->firstOrFail();
    }
}
