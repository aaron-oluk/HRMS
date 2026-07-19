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
        'employees.create',
        'employees.update',
        'employees.delete',
        'employees.view-salary',
        'employees.view-identity-numbers',
        'employees.view-bank-details',
        'employees.view-documents',
        'employees.manage-documents',
        'employments.manage',
        'users.manage',
        'leave.manage-types',
        'leave.approve',
        'attendance.manage-shifts',
        'attendance.approve-overtime',
        'attendance.view-team',
        'audit.view',
        'payroll.view',
        'payroll.view-team-summary',
        'payroll.view-own',
        'payroll.run',
        'payroll.approve',
        'payroll.disburse',
        'recruitment.view',
        'recruitment.manage',
        'recruitment.view-candidate-pii',
        'performance.view',
        'performance.manage-cycles',
        'performance.review',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
