<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    public const PERMISSIONS = [
        'org.view',
        'org.manage',
        'employees.view',
        'employees.manage',
        'employees.view-sensitive',
        'employments.manage',
        'users.manage',
        'leave.manage-types',
        'leave.approve',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
