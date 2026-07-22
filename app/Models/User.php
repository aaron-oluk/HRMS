<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'name',
        'email',
        'password',
        'status',
        'is_super_admin',
        'is_org_admin',
        'signature_path',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_org_admin' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * The tenants an Org Admin (is_org_admin) is scoped to. Meaningless for a Global
     * super admin (is_super_admin), who bypasses this entirely via Gate::before.
     */
    public function assignedTenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'platform_admin_tenants');
    }

    public function isPlatformAdmin(): bool
    {
        return $this->is_super_admin || $this->is_org_admin;
    }

    public function canAccessTenant(Tenant $tenant): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->is_org_admin && $this->assignedTenants()->where('tenants.id', $tenant->id)->exists();
    }
}
