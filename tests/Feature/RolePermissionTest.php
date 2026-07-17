<?php

use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;

test('a manager cannot see salary or national id fields on an employee profile', function () {
    [$tenant, $manager] = tenantWithRole('Manager');

    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create([
        'tenant_id' => $tenant->id,
        'national_id_number' => 'CM99999999999',
    ]);
    Employment::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'entity_id' => $entity->id,
        'basic_salary' => 4500000,
    ]);

    $this->actingAs($manager)
        ->get(route('employees.show', $employee))
        ->assertOk()
        ->assertDontSee('CM99999999999')
        ->assertDontSee('4,500,000');
});

test('an hr admin can see salary and national id fields on an employee profile', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');

    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create([
        'tenant_id' => $tenant->id,
        'national_id_number' => 'CM99999999999',
    ]);
    Employment::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'entity_id' => $entity->id,
        'basic_salary' => 4500000,
    ]);

    $this->actingAs($admin)
        ->get(route('employees.show', $employee))
        ->assertOk()
        ->assertSee('CM99999999999')
        ->assertSee('4,500,000');
});

test('a manager cannot manage org structure', function () {
    [$tenant, $manager] = tenantWithRole('Manager');

    $this->actingAs($manager)
        ->get(route('entities.create'))
        ->assertForbidden();

    $this->actingAs($manager)
        ->post(route('entities.store'), ['name' => 'New Co', 'currency' => 'UGX', 'status' => 'active'])
        ->assertForbidden();
});

test('an hr admin can manage org structure', function () {
    [, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)
        ->get(route('entities.create'))
        ->assertOk();
});
