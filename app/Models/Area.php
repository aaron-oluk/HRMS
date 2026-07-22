<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'entity_id',
        'name',
        'code',
        'status',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
