<?php

use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;

test('an hr specialist sees identity numbers but not salary or bank details', function () {
    [$tenant, $specialist] = tenantWithRole('HR Specialist');

    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create([
        'tenant_id' => $tenant->id,
        'national_id_number' => 'CM11111111111',
    ]);
    Employment::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'entity_id' => $entity->id,
        'basic_salary' => 4100000,
    ]);
    $employee->bankAccounts()->create([
        'bank_name' => 'Stanbic',
        'account_name' => 'Test Account',
        'account_number' => 'ACC-999999',
    ]);

    $this->actingAs($specialist)
        ->get(route('employees.show', $employee))
        ->assertOk()
        ->assertSee('CM11111111111')
        ->assertDontSee('4,100,000')
        ->assertDontSee('ACC-999999');
});

test('an accountant sees salary and bank details but not identity numbers', function () {
    [$tenant, $accountant] = tenantWithRole('Accountant');

    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create([
        'tenant_id' => $tenant->id,
        'national_id_number' => 'CM22222222222',
    ]);
    Employment::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'entity_id' => $entity->id,
        'basic_salary' => 4200000,
    ]);
    $employee->bankAccounts()->create([
        'bank_name' => 'Stanbic',
        'account_name' => 'Test Account',
        'account_number' => 'ACC-888888',
    ]);

    $this->actingAs($accountant)
        ->get(route('employees.show', $employee))
        ->assertOk()
        ->assertDontSee('CM22222222222')
        ->assertSee('4,200,000')
        ->assertSee('ACC-888888');
});

test('an auditor sees all sensitive fields read-only', function () {
    [$tenant, $auditor] = tenantWithRole('Auditor');

    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create([
        'tenant_id' => $tenant->id,
        'national_id_number' => 'CM33333333333',
    ]);
    Employment::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'entity_id' => $entity->id,
        'basic_salary' => 4300000,
    ]);

    $this->actingAs($auditor)
        ->get(route('employees.show', $employee))
        ->assertOk()
        ->assertSee('CM33333333333')
        ->assertSee('4,300,000')
        ->assertDontSee('Edit profile');
});
