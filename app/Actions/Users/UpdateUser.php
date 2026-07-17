<?php

namespace App\Actions\Users;

use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class UpdateUser
{
    public function __construct(protected PermissionRegistrar $permissionRegistrar) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): User
    {
        $role = $data['role'];
        unset($data['role']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        $this->permissionRegistrar->setPermissionsTeamId($user->tenant_id);
        $user->syncRoles([$role]);

        return $user;
    }
}
