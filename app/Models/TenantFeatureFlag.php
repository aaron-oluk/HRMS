<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantFeatureFlag extends Model
{
    /**
     * The optional modules a tenant's flags can toggle. Core modules (Employees, Leave,
     * Attendance, Dashboard, Inbox) aren't here — they have no gate to hook into and stay
     * permanently on.
     *
     * @var array<int, string>
     */
    public const MODULES = [
        'payroll',
        'recruitment',
        'performance',
        'engagement',
        'cases',
        'reports',
        'esignature',
    ];

    protected $fillable = [
        'tenant_id',
        'module',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
