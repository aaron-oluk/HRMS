<?php

namespace App\Actions\Tenancy;

use App\Models\Tenant;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ProvisionDefaultRoles
{
    /**
     * @var array<string, list<string>>
     */
    public const ROLE_PERMISSIONS = [
        // Tenant-wide admin (maps to DOC.md's "Tenant Admin") — every permission.
        'HR Admin' => [
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
            'payroll.run',
            'payroll.approve',
            'payroll.disburse',
            'recruitment.view',
            'recruitment.manage',
            'recruitment.view-candidate-pii',
            'performance.view',
            'performance.manage-cycles',
            'performance.review',
        ],
        // Full HR operations tenant-wide, except system/user administration and audit log access.
        'HR Manager' => [
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
            'leave.manage-types',
            'leave.approve',
            'attendance.manage-shifts',
            'attendance.approve-overtime',
            'attendance.view-team',
            'payroll.view',
            'payroll.run',
            'payroll.approve',
            'payroll.disburse',
            'recruitment.view',
            'recruitment.manage',
            'recruitment.view-candidate-pii',
            'performance.view',
            'performance.manage-cycles',
            'performance.review',
        ],
        // Operational HR: create/update employees, no delete, no salary/bank visibility.
        // Deliberately excluded from all payroll and performance-review permissions — DOC.md's
        // payroll matrix grants this role full payroll-run access, but that conflicts with its
        // established exclusion from employees.view-salary elsewhere in this file; the codebase's
        // existing salary-access precedent wins.
        'HR Specialist' => [
            'org.view',
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.view-identity-numbers',
            'employees.view-documents',
            'employees.manage-documents',
            'leave.manage-types',
            'leave.approve',
            'attendance.manage-shifts',
            'attendance.approve-overtime',
            'attendance.view-team',
            'recruitment.view',
            'recruitment.manage',
            'recruitment.view-candidate-pii',
        ],
        // Scoped to their own department (see TeamScope); no sensitive-field visibility.
        'Department Manager' => [
            'org.view',
            'employees.view',
            'employees.update',
            'employees.view-documents',
            'employees.manage-documents',
            'leave.approve',
            'attendance.manage-shifts',
            'attendance.approve-overtime',
            'attendance.view-team',
            'payroll.view-team-summary',
            'performance.view',
            'performance.review',
        ],
        // Scoped to direct reports only (see TeamScope); view-only, no edit rights.
        'Team Lead' => [
            'employees.view',
            'leave.approve',
            'attendance.approve-overtime',
            'attendance.view-team',
            'payroll.view-team-summary',
            'performance.view',
            'performance.review',
        ],
        // Read-only, tenant-wide, including sensitive fields, plus access-log visibility.
        'Auditor' => [
            'org.view',
            'employees.view',
            'employees.view-salary',
            'employees.view-identity-numbers',
            'employees.view-bank-details',
            'employees.view-documents',
            'audit.view',
            'payroll.view',
            'recruitment.view',
            'recruitment.view-candidate-pii',
        ],
        // Views employee financial data for payroll/disbursement purposes only.
        'Accountant' => [
            'employees.view',
            'employees.view-salary',
            'employees.view-bank-details',
            'audit.view',
            'payroll.view',
            'payroll.run',
            'payroll.disburse',
        ],
        'Employee' => [
            'payroll.view-own',
        ],
        // Bonus read-only role (not in DOC.md), unaffected by this rebuild.
        'Executive' => [
            'org.view',
            'employees.view',
            'payroll.view-team-summary',
            'recruitment.view',
        ],
    ];

    public function __construct(protected PermissionRegistrar $permissionRegistrar) {}

    /**
     * @return array<string, Role>
     */
    public function handle(Tenant $tenant): array
    {
        // Permissions are global (shared across every tenant), not tenant-scoped, and are
        // normally seeded once at deploy time via PermissionSeeder. Ensuring them here too
        // (idempotently — findOrCreate is a no-op once they exist) means onboarding a new
        // tenant never depends on that seeder having been run first.
        foreach (PermissionSeeder::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

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
