<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\CandidateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'review',
        'shortlisting',
        'interviews',
        'negotiations_and_offers',
        'contracts_and_appointments',
        'probation',
        'rejected',
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
}
