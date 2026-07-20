<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatutorySetting extends Model
{
    protected $fillable = [
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
     * The table is a singleton — one row, id 1, seeded by its migration.
     */
    public static function current(): self
    {
        return static::query()->firstOrFail();
    }
}
