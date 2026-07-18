<?php

use App\Models\Entity;
use App\Models\OvertimeRequest;

test('a manager can approve their direct report\'s overtime request', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Manager');
    [$reportEmployee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $managerEmployee);

    $request = OvertimeRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $reportEmployee->id, 'status' => 'pending']);

    $response = $this->actingAs($managerUser)->post(route('attendance.overtime.approve', $request));

    $response->assertRedirect(route('attendance.index'));
    expect($request->fresh()->status)->toBe('approved');
    expect($request->fresh()->approved_by)->toBe($managerUser->id);
});

test('a manager cannot approve an overtime request from outside their team', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [, $managerUser] = employeeUser($tenant, $entity, 'Manager');
    [$strangerEmployee] = employeeUser($tenant, $entity, 'Employee');

    $request = OvertimeRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $strangerEmployee->id, 'status' => 'pending']);

    $this->actingAs($managerUser)->post(route('attendance.overtime.approve', $request));

    expect($request->fresh()->status)->toBe('pending');
});

test('an hr admin can approve any overtime request in the tenant', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee] = employeeUser($tenant, $entity, 'Employee');

    $request = OvertimeRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $employee->id, 'status' => 'pending']);

    $this->actingAs($admin)->post(route('attendance.overtime.approve', $request));

    expect($request->fresh()->status)->toBe('approved');
});

test('an employee without attendance.approve-overtime permission cannot approve requests', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $employeeUserAccount] = employeeUser($tenant, $entity, 'Employee');

    $request = OvertimeRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $employee->id, 'status' => 'pending']);

    $this->actingAs($employeeUserAccount)->post(route('attendance.overtime.approve', $request))->assertForbidden();
});

test('rejecting an overtime request records the reason', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Manager');
    [$reportEmployee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $managerEmployee);

    $request = OvertimeRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $reportEmployee->id, 'status' => 'pending']);

    $this->actingAs($managerUser)->post(route('attendance.overtime.reject', $request), ['reason' => 'Budget exceeded']);

    expect($request->fresh()->status)->toBe('rejected');
    expect($request->fresh()->rejection_reason)->toBe('Budget exceeded');
});

test('an overtime request from another tenant is invisible via the global scope', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    [$employeeA] = employeeUser($tenantA, $entityA, 'Employee');
    $requestA = OvertimeRequest::factory()->create(['tenant_id' => $tenantA->id, 'employee_id' => $employeeA->id, 'status' => 'pending']);

    [$tenantB, $adminB] = tenantWithRole('HR Admin');

    $this->actingAs($adminB)->post(route('attendance.overtime.approve', $requestA))->assertNotFound();
});
