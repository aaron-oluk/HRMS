<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\JobRequisitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobRequisition extends Model
{
    /** @use HasFactory<JobRequisitionFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    /**
     * @var list<string>
     */
    public const TYPES = ['career', 'internship'];

    protected $fillable = [
        'tenant_id',
        'entity_id',
        'department_id',
        'position_id',
        'title',
        'type',
        'headcount',
        'status',
        'requested_by',
        'description',
        'opened_at',
        'closed_at',
    ];

    protected $attributes = [
        'type' => 'career',
        'status' => 'draft',
        'headcount' => 1,
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }
}
