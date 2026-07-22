<?php

namespace App\Actions\Tenancy;

use App\Actions\Users\CreateUser;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Onboards a new company: creates its Tenant record, provisions the default role
 * catalog for it, and creates its first user as HR Admin. Platform-admin-only
 * (see App\Http\Middleware\EnsureUserIsSuperAdmin) — there is no public signup.
 */
class CreateTenant
{
    public function __construct(
        protected ProvisionDefaultRoles $provisionDefaultRoles,
        protected CreateUser $createUser,
        protected SeedDefaultStatutoryConfig $seedDefaultStatutoryConfig,
    ) {}

    /**
     * @param  array{name: string, timezone: string, currency: string, structure?: string, admin_name: string, admin_email: string, admin_password: string}  $data
     */
    public function handle(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'timezone' => $data['timezone'],
                'currency' => $data['currency'],
                'structure' => $data['structure'] ?? 'simple',
            ]);

            $this->provisionDefaultRoles->handle($tenant);
            $this->seedDefaultStatutoryConfig->handle($tenant);

            $this->createUser->handle($tenant, [
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => $data['admin_password'],
                'status' => 'active',
                'role' => 'HR Admin',
            ]);

            return $tenant;
        });
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $suffix = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}
