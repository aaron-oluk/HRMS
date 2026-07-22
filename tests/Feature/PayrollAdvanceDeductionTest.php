<?php

use App\Actions\Payroll\GeneratePayrollRun;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeDeduction;
use App\Models\Entity;

test('an advance is deducted from net pay and its balance decrements across payroll runs until settled', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss] = employeeUser($tenant, $entity, 'Team Lead');
    [$employee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);
    $employee->currentEmployment->update(['basic_salary' => 1_000_000]);

    $advance = EmployeeAdvance::factory()->for($employee)->create([
        'tenant_id' => $tenant->id,
        'amount' => 300_000,
        'monthly_deduction' => 150_000,
        'balance_remaining' => 300_000,
        'status' => 'active',
    ]);

    $runOne = app(GeneratePayrollRun::class)->handle($entity, now()->startOfMonth()->toDateString(), $hrAdmin);
    $lineOne = $runOne->lines()->where('employee_id', $employee->id)->firstOrFail();

    expect((float) $lineOne->other_deductions)->toBe(150000.0);
    expect(round((float) $lineOne->net_pay, 2))->toBe(
        round((float) $lineOne->basic_salary - (float) $lineOne->paye_amount - (float) $lineOne->nssf_employee_amount - 150000, 2)
    );
    expect((float) $advance->fresh()->balance_remaining)->toBe(150000.0);
    expect($advance->fresh()->status)->toBe('active');

    $runTwo = app(GeneratePayrollRun::class)->handle($entity, now()->addMonth()->startOfMonth()->toDateString(), $hrAdmin);
    $lineTwo = $runTwo->lines()->where('employee_id', $employee->id)->firstOrFail();

    expect((float) $lineTwo->other_deductions)->toBe(150000.0);
    expect((float) $advance->fresh()->balance_remaining)->toBe(0.0);
    expect($advance->fresh()->status)->toBe('settled');
});

test('a one-time deduction is applied once then deactivated', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss] = employeeUser($tenant, $entity, 'Team Lead');
    [$employee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);

    $deduction = EmployeeDeduction::factory()->for($employee)->create([
        'tenant_id' => $tenant->id,
        'label' => 'Damaged equipment',
        'amount' => 50_000,
        'frequency' => 'one_time',
        'status' => 'active',
        'effective_date' => now()->startOfMonth()->toDateString(),
    ]);

    $runOne = app(GeneratePayrollRun::class)->handle($entity, now()->startOfMonth()->toDateString(), $hrAdmin);
    $lineOne = $runOne->lines()->where('employee_id', $employee->id)->firstOrFail();

    expect((float) $lineOne->other_deductions)->toBe(50000.0);
    expect($deduction->fresh()->status)->toBe('inactive');

    $runTwo = app(GeneratePayrollRun::class)->handle($entity, now()->addMonth()->startOfMonth()->toDateString(), $hrAdmin);
    $lineTwo = $runTwo->lines()->where('employee_id', $employee->id)->firstOrFail();

    expect((float) $lineTwo->other_deductions)->toBe(0.0);
});

test('a recurring deduction is applied on every payroll run', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss] = employeeUser($tenant, $entity, 'Team Lead');
    [$employee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);

    EmployeeDeduction::factory()->for($employee)->create([
        'tenant_id' => $tenant->id,
        'label' => 'Union dues',
        'amount' => 20_000,
        'frequency' => 'recurring',
        'status' => 'active',
        'effective_date' => now()->startOfMonth()->toDateString(),
    ]);

    $runOne = app(GeneratePayrollRun::class)->handle($entity, now()->startOfMonth()->toDateString(), $hrAdmin);
    $runTwo = app(GeneratePayrollRun::class)->handle($entity, now()->addMonth()->startOfMonth()->toDateString(), $hrAdmin);

    expect((float) $runOne->lines()->where('employee_id', $employee->id)->firstOrFail()->other_deductions)->toBe(20000.0);
    expect((float) $runTwo->lines()->where('employee_id', $employee->id)->firstOrFail()->other_deductions)->toBe(20000.0);
});

test('an hr specialist cannot record advances or deductions', function () {
    [$tenant, $specialist] = tenantWithRole('HR Specialist');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee] = employeeUser($tenant, $entity, 'Employee');

    $this->actingAs($specialist)->post(route('employees.advances.store', $employee), [
        'amount' => 100_000,
        'monthly_deduction' => 50_000,
        'issued_date' => now()->toDateString(),
    ])->assertForbidden();

    $this->actingAs($specialist)->post(route('employees.deductions.store', $employee), [
        'label' => 'Nope',
        'amount' => 10_000,
        'frequency' => 'one_time',
        'effective_date' => now()->toDateString(),
    ])->assertForbidden();
});

test('an hr admin can record and remove an advance', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee] = employeeUser($tenant, $entity, 'Employee');

    $this->actingAs($admin)->post(route('employees.advances.store', $employee), [
        'amount' => 200_000,
        'monthly_deduction' => 100_000,
        'issued_date' => now()->toDateString(),
    ])->assertRedirect(route('employees.show', $employee));

    $advance = EmployeeAdvance::where('employee_id', $employee->id)->firstOrFail();
    expect((float) $advance->balance_remaining)->toBe(200000.0);

    $this->actingAs($admin)->delete(route('employees.advances.destroy', [$employee, $advance]))
        ->assertRedirect(route('employees.show', $employee));

    expect(EmployeeAdvance::find($advance->id))->toBeNull();
});
