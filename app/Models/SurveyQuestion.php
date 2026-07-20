<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\SurveyQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyQuestion extends Model
{
    /** @use HasFactory<SurveyQuestionFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    public const TYPES = ['rating', 'text'];

    protected $fillable = [
        'tenant_id',
        'survey_id',
        'text',
        'type',
        'order',
    ];

    protected $attributes = [
        'type' => 'rating',
        'order' => 0,
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
}
