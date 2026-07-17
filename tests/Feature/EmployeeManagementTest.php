<?php

use App\Models\Employee;
use App\Models\Entity;

test('an hr admin can create an employee', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($admin)->post(route('employees.store'), [
        'entity_id' => $entity->id,
        'employee_number' => 'EMP-00099',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'status' => 'active',
    ]);

    $employee = Employee::where('employee_number', 'EMP-00099')->first();

    $response->assertRedirect(route('employees.show', $employee));
    expect($employee)->not->toBeNull();
    expect($employee->tenant_id)->toBe($tenant->id);
});

test('employee numbers must be unique within a tenant', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'employee_number' => 'EMP-00001']);

    $response = $this->actingAs($admin)->post(route('employees.store'), [
        'entity_id' => $entity->id,
        'employee_number' => 'EMP-00001',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'status' => 'active',
    ]);

    $response->assertSessionHasErrors('employee_number');
});

test('an employee number can be reused across different tenants', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    Employee::factory()->for($entityA)->create(['tenant_id' => $tenantA->id, 'employee_number' => 'EMP-00001']);

    [$tenantB, $adminB] = tenantWithRole('HR Admin');
    $entityB = Entity::factory()->create(['tenant_id' => $tenantB->id]);

    $response = $this->actingAs($adminB)->post(route('employees.store'), [
        'entity_id' => $entityB->id,
        'employee_number' => 'EMP-00001',
        'first_name' => 'John',
        'last_name' => 'Smith',
        'status' => 'active',
    ]);

    $response->assertSessionDoesntHaveErrors('employee_number');
});

test('a user without employees.manage permission cannot create an employee', function () {
    [$tenant, $employeeUser] = tenantWithRole('Employee');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($employeeUser)->post(route('employees.store'), [
        'entity_id' => $entity->id,
        'employee_number' => 'EMP-00099',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'status' => 'active',
    ])->assertForbidden();
});
