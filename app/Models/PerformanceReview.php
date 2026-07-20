<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\PerformanceReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceReview extends Model
{
    /** @use HasFactory<PerformanceReviewFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'performance_review_cycle_id',
        'employee_id',
        'reviewer_employee_id',
        'status',
        'self_rating',
        'self_comments',
        'self_submitted_at',
        'manager_rating',
        'manager_comments',
        'manager_submitted_at',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'self_submitted_at' => 'datetime',
            'manager_submitted_at' => 'datetime',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceReviewCycle::class, 'performance_review_cycle_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewer_employee_id');
    }

    public function feedbackRequests(): HasMany
    {
        return $this->hasMany(PerformanceFeedbackRequest::class, 'performance_review_id');
    }

    public function scopeAwaitingManagerReview(Builder $query): Builder
    {
        return $query->where('status', 'self_submitted');
    }
}
