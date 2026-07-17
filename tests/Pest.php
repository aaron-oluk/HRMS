<?php

use App\Actions\Tenancy\ProvisionDefaultRoles;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;
use App\Models\Position;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create a tenant with default roles provisioned and a user assigned the given role.
 * Also seeds the global permission catalog and sets the tenancy/permission context
 * for the current process, matching what IdentifyTenant middleware does per request.
 *
 * @return array{0: Tenant, 1: User}
 */
function tenantWithRole(string $role = 'HR Admin'): array
{
    foreach (PermissionSeeder::PERMISSIONS as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);

    $roles = app(ProvisionDefaultRoles::class)->handle($tenant);

    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $user->assignRole($roles[$role]);

    return [$tenant, $user];
}

/**
 * Create an Employee within the given tenant/entity, linked to a User with the given role.
 * Permissions must already be seeded (e.g. via a prior tenantWithRole() call).
 *
 * @return array{0: Employee, 1: User}
 */
function employeeUser(Tenant $tenant, Entity $entity, string $role, ?Employee $reportsTo = null): array
{
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    if ($reportsTo) {
        $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
        $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

        Employment::factory()->create([
            'tenant_id' => $tenant->id,
            'employee_id' => $employee->id,
            'entity_id' => $entity->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'reporting_to_employee_id' => $reportsTo->id,
        ]);
    }

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $roleModel = Role::where('tenant_id', $tenant->id)->where('name', $role)->firstOrFail();

    $user = User::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $employee->id]);
    $user->assignRole($roleModel);

    return [$employee, $user];
}
