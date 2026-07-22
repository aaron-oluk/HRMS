<?php

use App\Actions\Payroll\GeneratePayrollRun;
use App\Models\Entity;
use App\Models\PayrollRun;
use App\Support\Payroll\StatutoryEngine;
use App\Support\Tenancy\TenantContext;

test('the statutory engine computes PAYE and NSSF against DOC.md\'s published anchors', function () {
    [$tenant] = tenantWithRole('HR Admin');
    app(TenantContext::class)->set($tenant);

    $engine = new StatutoryEngine;

    // Below the 235,000 free threshold: no PAYE.
    expect($engine->payeFor(200_000))->toBe(0.0);

    // 10% band: 300,000 - 235,000 = 65,000 * 10% = 6,500.
    expect($engine->payeFor(300_000))->toBe(6500.0);

    // Base case used throughout Uganda's published quick-reference table.
    expect($engine->payeFor(1_000_000))->toBe(202000.0);

    // Above the 10,000,000 surcharge floor, the marginal rate is 30% + 10% = 40%.
    $justBelow = $engine->payeFor(10_000_000);
    $oneAboveMillion = $engine->payeFor(11_000_000);
    expect($oneAboveMillion - $justBelow)->toBe(400000.0);

    expect($engine->nssfEmployeeFor(1_000_000))->toBe(50000.0);
    expect($engine->nssfEmployerFor(1_000_000))->toBe(100000.0);
});

test('generating a payroll run creates one line per active employment', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss] = employeeUser($tenant, $entity, 'Team Lead');
    [$employeeA] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);
    [$employeeB] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);

    $run = app(GeneratePayrollRun::class)->handle($entity, now()->toDateString(), $hrAdmin);

    expect($run->status)->toBe('draft');
    expect($run->lines()->count())->toBe(2);

    $lineA = $run->lines()->where('employee_id', $employeeA->id)->firstOrFail();
    expect(round((float) $lineA->net_pay, 2))->toBe(
        round((float) $lineA->basic_salary - (float) $lineA->paye_amount - (float) $lineA->nssf_employee_amount, 2)
    );

    expect($run->lines()->where('employee_id', $employeeB->id)->exists())->toBeTrue();
});

test('a payroll run moves through draft, submitted, approved, and disbursed', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss] = employeeUser($tenant, $entity, 'Team Lead');
    employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);

    $this->actingAs($hrAdmin)->post(route('payroll.runs.store'), [
        'entity_id' => $entity->id,
        'period_month' => now()->format('Y-m-01'),
    ])->assertRedirect();

    $run = PayrollRun::where('entity_id', $entity->id)->firstOrFail();
    expect($run->status)->toBe('draft');

    $this->actingAs($hrAdmin)->post(route('payroll.runs.submit', $run));
    expect($run->fresh()->status)->toBe('pending_approval');

    $this->actingAs($hrAdmin)->post(route('payroll.runs.approve', $run));
    expect($run->fresh()->status)->toBe('approved');
    expect($run->fresh()->approved_by)->toBe($hrAdmin->id);

    $this->actingAs($hrAdmin)->post(route('payroll.runs.disburse', $run));
    expect($run->fresh()->status)->toBe('disbursed');
});

test('an accountant can run and disburse payroll but cannot approve it', function () {
    [$tenant, $accountant] = tenantWithRole('Accountant');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($accountant)->post(route('payroll.runs.store'), [
        'entity_id' => $entity->id,
        'period_month' => now()->format('Y-m-01'),
    ])->assertRedirect();

    $run = PayrollRun::where('entity_id', $entity->id)->firstOrFail();

    $this->actingAs($accountant)->post(route('payroll.runs.submit', $run));
    expect($run->fresh()->status)->toBe('pending_approval');

    $this->actingAs($accountant)->post(route('payroll.runs.approve', $run))->assertForbidden();
    expect($run->fresh()->status)->toBe('pending_approval');
});

test('an hr specialist has no payroll access, consistent with lacking salary visibility elsewhere', function () {
    [$tenant, $specialist] = tenantWithRole('HR Specialist');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($specialist)->post(route('payroll.runs.store'), [
        'entity_id' => $entity->id,
        'period_month' => now()->format('Y-m-01'),
    ])->assertForbidden();

    $this->actingAs($specialist)->get(route('payroll.runs.index'))->assertForbidden();
});

test('a department manager sees an aggregate summary, not individual salary lines', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$deptManagerEmployee, $deptManagerUser] = employeeUser($tenant, $entity, 'Department Manager');
    [$report] = employeeUser($tenant, $entity, 'Employee', reportsTo: $deptManagerEmployee);

    // Give the report an employment in the same department as their manager, since
    // department-scoping keys off Employment.department_id, not the reporting line.
    $department = $report->currentEmployment->department;
    $deptManagerEmployee->employments()->create([
        'tenant_id' => $tenant->id,
        'entity_id' => $entity->id,
        'department_id' => $department->id,
        'position_id' => $report->currentEmployment->position_id,
        'basic_salary' => 4000000,
        'effective_from' => now()->subYear()->toDateString(),
        'status' => 'active',
    ]);

    $run = app(GeneratePayrollRun::class)->handle($entity, now()->toDateString(), $hrAdmin);

    $response = $this->actingAs($deptManagerUser)->get(route('payroll.runs.show', $run));

    $response->assertOk();
    $response->assertSee('Headcount');
    $response->assertDontSee($report->fullName());
});

test('a payroll run from another tenant is invisible via the global scope', function () {
    [$tenantA, $hrAdminA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    $runA = app(GeneratePayrollRun::class)->handle($entityA, now()->toDateString(), $hrAdminA);

    [, $hrAdminB] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdminB)->get(route('payroll.runs.show', $runA))->assertNotFound();
});
