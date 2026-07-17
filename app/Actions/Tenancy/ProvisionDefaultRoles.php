<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ProvisionDefaultRoles
{
    /**
     * @var array<string, list<string>>
     */
    public const ROLE_PERMISSIONS = [
        'HR Admin' => [
            'org.view',
            'org.manage',
            'employees.view',
            'employees.manage',
            'employees.view-sensitive',
            'employments.manage',
            'users.manage',
        ],
        'Manager' => [
            'org.view',
            'employees.view',
        ],
        'Employee' => [],
    ];

    public function __construct(protected PermissionRegistrar $permissionRegistrar) {}

    /**
     * @return array<string, Role>
     */
    public function handle(Tenant $tenant): array
    {
        $this->permissionRegistrar->setPermissionsTeamId($tenant->id);

        $roles = [];

        foreach (self::ROLE_PERMISSIONS as $name => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ]);

            $role->syncPermissions($permissions);

            $roles[$name] = $role;
        }

        return $roles;
    }
}
