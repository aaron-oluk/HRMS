<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\StatutoryPayeBandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutoryPayeBand extends Model
{
    /** @use HasFactory<StatutoryPayeBandFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'floor',
        'rate',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'floor' => 'decimal:2',
            'rate' => 'decimal:4',
        ];
    }
}
