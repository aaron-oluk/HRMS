<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\HrCaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrCase extends Model
{
    /** @use HasFactory<HrCaseFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    public const CATEGORIES = ['payroll', 'benefits', 'facilities', 'general', 'other'];

    protected $table = 'hr_cases';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'category',
        'subject',
        'description',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    protected $attributes = [
        'category' => 'general',
        'status' => 'open',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(HrCaseComment::class);
    }
}
