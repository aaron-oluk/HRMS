<?php

use App\Models\Employee;
use App\Models\Entity;

test('a tenant cannot see another tenant\'s employees via the global scope', function () {
    [$tenantA, $adminA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    $employeeA = Employee::factory()->for($entityA)->create(['tenant_id' => $tenantA->id]);

    [$tenantB] = tenantWithRole('HR Admin');
    $entityB = Entity::factory()->create(['tenant_id' => $tenantB->id]);
    $employeeB = Employee::factory()->for($entityB)->create(['tenant_id' => $tenantB->id]);

    $this->actingAs($adminA)
        ->get(route('employees.index'))
        ->assertOk()
        ->assertSee($employeeA->employee_number)
        ->assertDontSee($employeeB->employee_number);
});

test('a tenant cannot fetch another tenant\'s employee by id', function () {
    [$tenantA, $adminA] = tenantWithRole('HR Admin');

    [$tenantB] = tenantWithRole('HR Admin');
    $entityB = Entity::factory()->create(['tenant_id' => $tenantB->id]);
    $employeeB = Employee::factory()->for($entityB)->create(['tenant_id' => $tenantB->id]);

    $this->actingAs($adminA)
        ->get(route('employees.show', $employeeB))
        ->assertNotFound();
});

test('creating a record without an explicit tenant_id assigns the current tenant context', function () {
    [$tenant] = tenantWithRole('HR Admin');

    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => null]);

    expect($employee->tenant_id)->toBe($tenant->id);
});
