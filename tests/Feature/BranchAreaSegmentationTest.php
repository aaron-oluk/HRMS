<?php

use App\Models\Area;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;
use App\Models\Position;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * @return array{0: Employee, 1: User}
 */
function branchScopedUser(string $role, Tenant $tenant, Branch $branch): array
{
    $entity = $branch->entity;
    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    Employment::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'entity_id' => $entity->id,
        'branch_id' => $branch->id,
        'position_id' => $position->id,
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $roleModel = Role::where('tenant_id', $tenant->id)->where('name', $role)->firstOrFail();

    $user = User::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $employee->id]);
    $user->assignRole($roleModel);

    return [$employee, $user];
}

test('a branch manager only sees employees in their own branch', function () {
    [$tenant] = tenantWithRole('HR Admin');
    app(TenantContext::class)->set($tenant);
    $tenant->update(['structure' => 'segmented']);

    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $branchA = Branch::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $branchB = Branch::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    [$managerEmployee, $manager] = branchScopedUser('Branch Manager', $tenant, $branchA);

    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $peerInBranchA = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'first_name' => 'Alice']);
    Employment::factory()->create([
        'tenant_id' => $tenant->id, 'employee_id' => $peerInBranchA->id, 'entity_id' => $entity->id,
        'branch_id' => $branchA->id, 'position_id' => $position->id,
    ]);

    $employeeInBranchB = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'first_name' => 'Bob']);
    Employment::factory()->create([
        'tenant_id' => $tenant->id, 'employee_id' => $employeeInBranchB->id, 'entity_id' => $entity->id,
        'branch_id' => $branchB->id, 'position_id' => $position->id,
    ]);

    $response = $this->actingAs($manager)->get(route('employees.index'))->assertOk();

    $response->assertSee($managerEmployee->fullName());
    $response->assertSee('Alice');
    $response->assertDontSee('Bob');
    $response->assertSee($branchA->name);
});

test('an area manager sees every branch in their own area', function () {
    [$tenant] = tenantWithRole('HR Admin');
    app(TenantContext::class)->set($tenant);
    $tenant->update(['structure' => 'segmented']);

    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $area = Area::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $otherArea = Area::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $branchInArea1 = Branch::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'area_id' => $area->id]);
    $branchInArea2 = Branch::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'area_id' => $area->id]);
    $branchInOtherArea = Branch::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'area_id' => $otherArea->id]);

    [, $manager] = branchScopedUser('Area Manager', $tenant, $branchInArea1);

    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $peerInArea = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'first_name' => 'Carla']);
    Employment::factory()->create([
        'tenant_id' => $tenant->id, 'employee_id' => $peerInArea->id, 'entity_id' => $entity->id,
        'branch_id' => $branchInArea2->id, 'position_id' => $position->id,
    ]);

    $employeeOutsideArea = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'first_name' => 'Derek']);
    Employment::factory()->create([
        'tenant_id' => $tenant->id, 'employee_id' => $employeeOutsideArea->id, 'entity_id' => $entity->id,
        'branch_id' => $branchInOtherArea->id, 'position_id' => $position->id,
    ]);

    $response = $this->actingAs($manager)->get(route('employees.index'))->assertOk();

    $response->assertSee('Carla');
    $response->assertDontSee('Derek');
    $response->assertSee($area->name);
});

test('hr admin still sees every employee regardless of branch or area', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    app(TenantContext::class)->set($tenant);

    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $branchA = Branch::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $branchB = Branch::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $employeeA = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'first_name' => 'InBranchA']);
    Employment::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $employeeA->id, 'entity_id' => $entity->id, 'branch_id' => $branchA->id, 'position_id' => $position->id]);

    $employeeB = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'first_name' => 'InBranchB']);
    Employment::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $employeeB->id, 'entity_id' => $entity->id, 'branch_id' => $branchB->id, 'position_id' => $position->id]);

    $response = $this->actingAs($admin)->get(route('employees.index'))->assertOk();

    $response->assertSee('InBranchA');
    $response->assertSee('InBranchB');
});

test('branch manager and area manager roles are hidden from the user form on a simple-structure tenant', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $tenant->update(['structure' => 'simple']);

    $response = $this->actingAs($admin)->get(route('users.create'))->assertOk();
    $response->assertDontSee('Branch Manager');
    $response->assertDontSee('Area Manager');
});

test('branch manager and area manager roles appear in the user form on a segmented tenant', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $tenant->update(['structure' => 'segmented']);

    $response = $this->actingAs($admin)->get(route('users.create'))->assertOk();
    $response->assertSee('Branch Manager');
    $response->assertSee('Area Manager');
});
