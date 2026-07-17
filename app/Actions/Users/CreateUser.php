<?php

namespace App\Actions\Users;

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class CreateUser
{
    public function __construct(protected PermissionRegistrar $permissionRegistrar) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Tenant $tenant, array $data): User
    {
        $role = $data['role'];
        unset($data['role']);

        $user = User::create([
            ...$data,
            'tenant_id' => $tenant->id,
        ]);

        $this->permissionRegistrar->setPermissionsTeamId($tenant->id);
        $user->assignRole($role);

        return $user;
    }
}
