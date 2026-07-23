<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\CandidateCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateComment extends Model
{
    /** @use HasFactory<CandidateCommentFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'candidate_id',
        'body',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
