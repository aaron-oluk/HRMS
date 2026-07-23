<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\CandidateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    /** @use HasFactory<CandidateFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    /**
     * Valid pipeline stages, in order.
     *
     * @var list<string>
     */
    public const STATUSES = [
        'advertising',
        'applied',
        'review',
        'shortlisting',
        'interviews',
        'negotiations_and_offers',
        'contracts_and_appointments',
        'probation',
        'hired',
        'rejected',
    ];

    /**
     * The real sequential pipeline, excluding 'rejected' — an escape hatch reachable from any
     * stage, not the next step after 'probation'. Drives nextStatus() below.
     *
     * @var list<string>
     */
    public const PIPELINE_SEQUENCE = [
        'advertising',
        'applied',
        'review',
        'shortlisting',
        'interviews',
        'negotiations_and_offers',
        'contracts_and_appointments',
        'probation',
        'hired',
    ];

    protected $fillable = [
        'tenant_id',
        'job_requisition_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'resume_path',
        'source',
        'status',
        'rating',
        'notes',
    ];

    protected $attributes = [
        'status' => 'advertising',
    ];

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function jobRequisition(): BelongsTo
    {
        return $this->belongsTo(JobRequisition::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CandidateComment::class)->latest();
    }

    /**
     * Null for a candidate who applied through the public job-application API — there's no
     * authenticated user in that request for Userstamped to attribute created_by to.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The next stage in the real sequential pipeline, or null when there isn't one — already
     * rejected (not part of the sequence) or already at the final 'probation' stage.
     */
    public function nextStatus(): ?string
    {
        $index = array_search($this->status, self::PIPELINE_SEQUENCE, true);

        if ($index === false || $index === count(self::PIPELINE_SEQUENCE) - 1) {
            return null;
        }

        return self::PIPELINE_SEQUENCE[$index + 1];
    }
}
