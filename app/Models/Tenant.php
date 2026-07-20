<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'timezone',
        'currency',
    ];

    protected $attributes = [
        'status' => 'active',
        'timezone' => 'Africa/Kampala',
        'currency' => 'UGX',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function featureFlags(): HasMany
    {
        return $this->hasMany(TenantFeatureFlag::class);
    }

    /**
     * A module with no row is enabled by default, so every existing tenant keeps working
     * with zero migration data needed the moment a new flaggable module ships.
     */
    public function hasModule(string $module): bool
    {
        return $this->featureFlags
            ->firstWhere('module', $module)
            ?->enabled ?? true;
    }
}
