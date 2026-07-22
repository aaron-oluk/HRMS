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
            'employees.manage-compensation',
            'employees.view-notes',
            'employees.manage-notes',
            'employees.view-warnings',
            'employees.manage-warnings',
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
            'engagement.manage',
            'cases.manage',
            'reports.view',
            'esignature.send',
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
            'employees.manage-compensation',
            'employees.view-notes',
            'employees.manage-notes',
            'employees.view-warnings',
            'employees.manage-warnings',
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
            'engagement.manage',
            'cases.manage',
            'reports.view',
            'esignature.send',
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
            'employees.view-notes',
            'employees.manage-notes',
            'employees.view-warnings',
            'employees.manage-warnings',
            'leave.manage-types',
            'leave.approve',
            'attendance.manage-shifts',
            'attendance.approve-overtime',
            'attendance.view-team',
            'recruitment.view',
            'recruitment.manage',
            'recruitment.view-candidate-pii',
            'cases.manage',
            'esignature.send',
        ],
        // Scoped to their own department (see TeamScope); no sensitive-field visibility.
        'Department Manager' => [
            'org.view',
            'employees.view',
            'employees.update',
            'employees.view-documents',
            'employees.manage-documents',
            'employees.view-notes',
            'employees.manage-notes',
            'employees.view-warnings',
            'employees.manage-warnings',
            'leave.approve',
            'attendance.manage-shifts',
            'attendance.approve-overtime',
            'attendance.view-team',
            'payroll.view-team-summary',
            'performance.view',
            'performance.review',
        ],
        // Segmented-structure only (see Tenant::isSegmented()) — scoped to their own branch
        // (see TeamScope), derived from their own currentEmployment->branch_id. Meaningless
        // on a Simple-structure tenant, same precedent as the "Executive" bonus role below.
        'Branch Manager' => [
            'org.view',
            'employees.view',
            'employees.update',
            'employees.view-documents',
            'employees.manage-documents',
            'employees.view-notes',
            'employees.manage-notes',
            'employees.view-warnings',
            'employees.manage-warnings',
            'leave.approve',
            'attendance.manage-shifts',
            'attendance.approve-overtime',
            'attendance.view-team',
            'payroll.view-team-summary',
            'performance.view',
            'performance.review',
        ],
        // Segmented-structure only — scoped to every branch under their own area (see
        // TeamScope), derived from their own currentEmployment->branch->area_id.
        'Area Manager' => [
            'org.view',
            'employees.view',
            'employees.update',
            'employees.view-documents',
            'employees.manage-documents',
            'employees.view-notes',
            'employees.manage-notes',
            'employees.view-warnings',
            'employees.manage-warnings',
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
            'employees.view-notes',
            'employees.view-warnings',
            'audit.view',
            'payroll.view',
            'recruitment.view',
            'recruitment.view-candidate-pii',
            'reports.view',
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
            'reports.view',
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
            'reports.view',
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
