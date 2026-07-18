<?php

use App\Actions\Leave\SubmitLeaveRequest;
use App\Models\Employee;
use App\Models\Entity;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Tenant;
use App\Support\Leave\LeaveBalance;

function submitPendingLeave(Tenant $tenant, Employee $employee, LeaveType $leaveType): LeaveRequest
{
    $monday = now()->next('Monday');

    return app(SubmitLeaveRequest::class)->handle($employee, [
        'leave_type_id' => $leaveType->id,
        'start_date' => $monday->toDateString(),
        'end_date' => $monday->copy()->addDays(2)->toDateString(),
        'reason' => 'Test',
    ]);
}

test('a manager can approve their direct report\'s leave request', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$reportEmployee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $managerEmployee);

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);
    $request = submitPendingLeave($tenant, $reportEmployee, $leaveType);

    $response = $this->actingAs($managerUser)->post(route('leave.approve', $request));

    $response->assertRedirect(route('leave.index'));
    expect($request->fresh()->status)->toBe('approved');
    expect($request->fresh()->approved_by)->toBe($managerUser->id);
});

test('a manager cannot approve a request from outside their team', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$strangerEmployee] = employeeUser($tenant, $entity, 'Employee'); // no reportsTo

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);
    $request = submitPendingLeave($tenant, $strangerEmployee, $leaveType);

    $this->actingAs($managerUser)->post(route('leave.approve', $request));

    expect($request->fresh()->status)->toBe('pending');
});

test('an hr admin can approve any request in the tenant', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee] = employeeUser($tenant, $entity, 'Employee');

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);
    $request = submitPendingLeave($tenant, $employee, $leaveType);

    $this->actingAs($admin)->post(route('leave.approve', $request));

    expect($request->fresh()->status)->toBe('approved');
});

test('an employee without leave.approve permission cannot approve requests', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $employeeUserAccount] = employeeUser($tenant, $entity, 'Employee');

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);
    $request = submitPendingLeave($tenant, $employee, $leaveType);

    $this->actingAs($employeeUserAccount)->post(route('leave.approve', $request))->assertForbidden();
});

test('approving a request reduces the computed available balance', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$reportEmployee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $managerEmployee);

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'default_days_per_year' => 10]);
    $request = submitPendingLeave($tenant, $reportEmployee, $leaveType);

    $balance = app(LeaveBalance::class);
    expect($balance->available($reportEmployee, $leaveType, now()->year))->toBe(10.0);

    $this->actingAs($managerUser)->post(route('leave.approve', $request));

    expect($balance->available($reportEmployee, $leaveType->fresh(), now()->year))->toBe(7.0);
});

test('a rejected request does not affect the balance', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$reportEmployee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $managerEmployee);

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'default_days_per_year' => 10]);
    $request = submitPendingLeave($tenant, $reportEmployee, $leaveType);

    $this->actingAs($managerUser)->post(route('leave.reject', $request), ['reason' => 'Not enough coverage']);

    expect($request->fresh()->status)->toBe('rejected');
    expect($request->fresh()->rejection_reason)->toBe('Not enough coverage');

    $balance = app(LeaveBalance::class);
    expect($balance->available($reportEmployee, $leaveType, now()->year))->toBe(10.0);
});

test('a leave request from another tenant is invisible via the global scope', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    [$employeeA] = employeeUser($tenantA, $entityA, 'Employee');
    $typeA = LeaveType::factory()->create(['tenant_id' => $tenantA->id, 'entity_id' => $entityA->id]);
    $requestA = submitPendingLeave($tenantA, $employeeA, $typeA);

    [$tenantB, $adminB] = tenantWithRole('HR Admin');

    $this->actingAs($adminB)->post(route('leave.approve', $requestA))->assertNotFound();
});
