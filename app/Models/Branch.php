<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\Userstamped;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use Auditable, BelongsToTenant, HasFactory, Userstamped;

    protected $fillable = [
        'tenant_id',
        'entity_id',
        'area_id',
        'name',
        'code',
        'address',
        'status',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
