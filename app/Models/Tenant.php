<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'structure',
        'theme_id',
    ];

    protected $attributes = [
        'status' => 'active',
        'timezone' => 'Africa/Kampala',
        'currency' => 'UGX',
        'structure' => 'simple',
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

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
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

    /**
     * Segmented tenants have opted into Head Office > Area > Branch structure (see
     * App\Models\Area) — this gates Area management UI, the Branch/Area Manager roles,
     * and the location-restricted employee search. Simple tenants see none of it.
     */
    public function isSegmented(): bool
    {
        return $this->structure === 'segmented';
    }

    /**
     * Falls back to the platform default theme when no theme is explicitly picked.
     */
    public function activeTheme(): Theme
    {
        return $this->theme ?? Theme::default();
    }
}
