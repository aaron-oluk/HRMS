<?php

use App\Models\Employee;
use App\Models\EmployeeInsurance;
use App\Models\Entity;

test('an hr admin can add an insurance policy to an employee', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->post(route('employees.insurances.store', $employee), [
        'provider' => 'Jubilee Health Insurance',
        'policy_number' => 'JHI-2024-00123',
        'type' => 'medical',
        'start_date' => now()->toDateString(),
    ])->assertRedirect(route('employees.show', $employee));

    $insurance = EmployeeInsurance::where('employee_id', $employee->id)->firstOrFail();
    expect($insurance->provider)->toBe('Jubilee Health Insurance');
    expect($insurance->tenant_id)->toBe($tenant->id);
});

test('an hr admin can remove an insurance policy', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $insurance = EmployeeInsurance::factory()->for($employee)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->delete(route('employees.insurances.destroy', [$employee, $insurance]))
        ->assertRedirect(route('employees.show', $employee));

    expect(EmployeeInsurance::find($insurance->id))->toBeNull();
});

test('a team lead cannot view or manage insurance policies', function () {
    [$tenant, $teamLead] = tenantWithRole('Team Lead');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    EmployeeInsurance::factory()->for($employee)->create(['tenant_id' => $tenant->id, 'provider' => 'Secret Insurer']);

    $this->actingAs($teamLead)->get(route('employees.show', $employee))
        ->assertOk()
        ->assertDontSee('Secret Insurer');

    $this->actingAs($teamLead)->post(route('employees.insurances.store', $employee), [
        'provider' => 'Nope',
        'policy_number' => 'Nope',
        'type' => 'medical',
        'start_date' => now()->toDateString(),
    ])->assertForbidden();
});

test('insurance requires a valid type', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->post(route('employees.insurances.store', $employee), [
        'provider' => 'Jubilee Health Insurance',
        'policy_number' => 'JHI-2024-00123',
        'type' => 'not-a-real-type',
        'start_date' => now()->toDateString(),
    ])->assertSessionHasErrors('type');
});
