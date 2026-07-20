<?php

use App\Models\Employee;
use App\Models\EmployeeCompensationItem;
use App\Models\Entity;
use App\Models\LeaveType;

test('the employee overview tab renders leave balances, hours, and calendar widgets', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    LeaveType::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'name' => 'Annual Leave', 'default_days_per_year' => 20]);

    $response = $this->actingAs($admin)->get(route('employees.show', $employee))->assertOk();

    $response->assertSee('Leave balance');
    $response->assertSee('All Leaves');
    $response->assertSee('Annual Leave');
    $response->assertSee('Hours logged');
    $response->assertSee('Performance overview');
});

test('the payroll summary widget is hidden without employees.view-salary', function () {
    [$tenant, $specialist] = tenantWithRole('HR Specialist');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $response = $this->actingAs($specialist)->get(route('employees.show', $employee))->assertOk();

    $response->assertDontSee('Payroll summary');
});

test('the payroll summary widget totals base salary, allowances, and benefits', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    EmployeeCompensationItem::factory()->for($employee)->allowance()->create([
        'tenant_id' => $tenant->id, 'name' => 'Transport', 'amount' => 100_000,
    ]);
    EmployeeCompensationItem::factory()->for($employee)->benefit()->create([
        'tenant_id' => $tenant->id, 'name' => 'Health Insurance', 'amount' => 50_000,
    ]);

    $response = $this->actingAs($admin)->get(route('employees.show', $employee))->assertOk();

    $response->assertSee('Payroll summary');
    $response->assertSee('Transport');
    $response->assertSee('Health Insurance');
    $response->assertSee('Total Monthly Value');
});
