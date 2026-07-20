<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\SurveyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'is_anonymous',
        'status',
        'created_by',
        'closes_at',
    ];

    protected $attributes = [
        'status' => 'draft',
        'is_anonymous' => false,
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'closes_at' => 'datetime',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
