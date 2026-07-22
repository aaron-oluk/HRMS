<?php

use App\Models\Employee;
use App\Models\EmployeeWarning;
use App\Models\Entity;

test('an hr admin can issue a warning to an employee', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->post(route('employees.warnings.store', $employee), [
        'severity' => 'written',
        'reason' => 'Repeated unexcused absences.',
        'issued_at' => now()->toDateString(),
    ])->assertRedirect(route('employees.show', $employee));

    $warning = EmployeeWarning::where('employee_id', $employee->id)->firstOrFail();
    expect($warning->severity)->toBe('written');
    expect($warning->issued_by)->toBe($admin->id);
    expect($warning->tenant_id)->toBe($tenant->id);
});

test('an hr admin can remove a warning', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $warning = EmployeeWarning::factory()->for($employee)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->delete(route('employees.warnings.destroy', [$employee, $warning]))
        ->assertRedirect(route('employees.show', $employee));

    expect(EmployeeWarning::find($warning->id))->toBeNull();
});

test('a team lead cannot issue or view warnings', function () {
    [$tenant, $teamLead] = tenantWithRole('Team Lead');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    EmployeeWarning::factory()->for($employee)->create(['tenant_id' => $tenant->id, 'reason' => 'Confidential warning']);

    $this->actingAs($teamLead)->get(route('employees.show', $employee))
        ->assertOk()
        ->assertDontSee('Confidential warning');

    $this->actingAs($teamLead)->post(route('employees.warnings.store', $employee), [
        'severity' => 'verbal',
        'reason' => 'Nope',
        'issued_at' => now()->toDateString(),
    ])->assertForbidden();
});

test('the employee who received a warning can acknowledge it themselves', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $user] = employeeUser($tenant, $entity, 'Employee');
    $warning = EmployeeWarning::factory()->for($employee)->create(['tenant_id' => $tenant->id, 'issued_by' => $admin->id]);

    $this->actingAs($user)->post(route('employees.warnings.acknowledge', [$employee, $warning]))
        ->assertRedirect(route('employees.show', $employee));

    expect($warning->fresh()->acknowledged_at)->not->toBeNull();
});

test('an employee cannot acknowledge someone else\'s warning', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [, $user] = employeeUser($tenant, $entity, 'Employee');
    $otherEmployee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $warning = EmployeeWarning::factory()->for($otherEmployee)->create(['tenant_id' => $tenant->id, 'issued_by' => $admin->id]);

    $this->actingAs($user)->post(route('employees.warnings.acknowledge', [$otherEmployee, $warning]))
        ->assertForbidden();
});

test('an employee can view their own warnings via the my-warnings page', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $user] = employeeUser($tenant, $entity, 'Employee');
    EmployeeWarning::factory()->for($employee)->create(['tenant_id' => $tenant->id, 'issued_by' => $admin->id, 'reason' => 'Late submission of timesheets']);

    $this->actingAs($user)->get(route('warnings.mine'))
        ->assertOk()
        ->assertSee('Late submission of timesheets');
});

test('warnings require a valid severity', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->post(route('employees.warnings.store', $employee), [
        'severity' => 'not-a-real-severity',
        'reason' => 'Nope',
        'issued_at' => now()->toDateString(),
    ])->assertSessionHasErrors('severity');
});
