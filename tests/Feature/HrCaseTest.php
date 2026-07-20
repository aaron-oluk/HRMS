<?php

use App\Models\Entity;
use App\Models\HrCase;

test('an employee can submit and view their own case', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $employeeUser] = employeeUser($tenant, $entity, 'Employee');

    $this->actingAs($employeeUser)->post(route('cases.store'), [
        'category' => 'payroll',
        'subject' => 'Payslip question',
        'description' => 'My NSSF deduction looks off.',
    ])->assertRedirect();

    $case = HrCase::where('employee_id', $employee->id)->firstOrFail();
    $this->actingAs($employeeUser)->get(route('cases.show', $case))->assertOk();
});

test('an employee cannot view another employee\'s case', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employeeA] = employeeUser($tenant, $entity, 'Employee');
    [, $employeeUserB] = employeeUser($tenant, $entity, 'Employee');

    $case = HrCase::create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employeeA->id,
        'category' => 'general',
        'subject' => 'Private matter',
        'description' => 'Confidential.',
    ]);

    $this->actingAs($employeeUserB)->get(route('cases.show', $case))->assertForbidden();
});

test('hr can assign and resolve a case, and an internal note stays hidden from the employee', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $employeeUser] = employeeUser($tenant, $entity, 'Employee');

    $case = HrCase::create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'category' => 'general',
        'subject' => 'Question',
        'description' => 'Details.',
    ]);

    $this->actingAs($hrAdmin)->post(route('cases.assign', $case), ['assigned_to' => $hrAdmin->id])->assertRedirect();
    expect($case->fresh()->assigned_to)->toBe($hrAdmin->id);
    expect($case->fresh()->status)->toBe('in_progress');

    $this->actingAs($hrAdmin)->post(route('cases.comment', $case), [
        'body' => 'Internal note for HR only.',
        'is_internal' => '1',
    ])->assertRedirect();

    $this->actingAs($hrAdmin)->post(route('cases.resolve', $case))->assertRedirect();
    expect($case->fresh()->status)->toBe('resolved');

    $employeeView = $this->actingAs($employeeUser)->get(route('cases.show', $case));
    $employeeView->assertDontSee('Internal note for HR only.');

    $hrView = $this->actingAs($hrAdmin)->get(route('cases.show', $case));
    $hrView->assertSee('Internal note for HR only.');
});

test('an employee cannot mark a case internal or assign it', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $employeeUser] = employeeUser($tenant, $entity, 'Employee');

    $case = HrCase::create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'category' => 'general',
        'subject' => 'Question',
        'description' => 'Details.',
    ]);

    $this->actingAs($employeeUser)->post(route('cases.assign', $case), ['assigned_to' => $employeeUser->id])->assertForbidden();

    $this->actingAs($employeeUser)->post(route('cases.comment', $case), [
        'body' => 'Trying to sneak an internal note.',
        'is_internal' => '1',
    ]);

    expect($case->comments()->where('is_internal', true)->exists())->toBeFalse();
});

test('a case from another tenant is invisible via the global scope', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    [$employeeA] = employeeUser($tenantA, $entityA, 'Employee');
    $caseA = HrCase::create([
        'tenant_id' => $tenantA->id,
        'employee_id' => $employeeA->id,
        'category' => 'general',
        'subject' => 'A',
        'description' => 'A',
    ]);

    [, $hrAdminB] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdminB)->get(route('cases.show', $caseA))->assertNotFound();
});
