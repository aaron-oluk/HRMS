<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\PerformanceGoalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceGoal extends Model
{
    /** @use HasFactory<PerformanceGoalFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    public const STATUSES = ['on_track', 'at_risk', 'off_track', 'completed'];

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'performance_review_cycle_id',
        'title',
        'description',
        'target_value',
        'current_value',
        'unit',
        'status',
        'due_date',
    ];

    protected $attributes = [
        'status' => 'on_track',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'current_value' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceReviewCycle::class, 'performance_review_cycle_id');
    }
}
