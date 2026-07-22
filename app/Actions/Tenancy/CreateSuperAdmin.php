<?php

namespace App\Actions\Tenancy;

use App\Models\User;

class CreateSuperAdmin
{
    /**
     * @param  array{name: string, email: string, password: string, tier: string, tenant_ids?: array<int, int>}  $data
     */
    public function handle(array $data): User
    {
        $isGlobal = $data['tier'] === 'global';

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'tenant_id' => null,
            'is_super_admin' => $isGlobal,
            'is_org_admin' => ! $isGlobal,
        ]);

        if (! $isGlobal) {
            $user->assignedTenants()->sync($data['tenant_ids'] ?? []);
        }

        return $user;
    }
}
