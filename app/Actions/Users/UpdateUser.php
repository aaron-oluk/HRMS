<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Support\Audit\AccessAudit;
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
        $previousRole = $user->getRoleNames()->first();
        $user->syncRoles([$role]);

        if ($previousRole !== $role && ($actor = auth()->user())) {
            AccessAudit::roleAssigned($actor, $user, $role);
        }

        return $user;
    }
}
