<?php

use App\Models\Entity;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OvertimeRequest;

test('a manager sees only their team\'s pending leave and overtime requests in the inbox', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Manager');
    [$reportEmployee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $managerEmployee);
    [$strangerEmployee] = employeeUser($tenant, $entity, 'Employee');

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);
    LeaveRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $reportEmployee->id, 'leave_type_id' => $leaveType->id, 'status' => 'pending']);
    LeaveRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $strangerEmployee->id, 'leave_type_id' => $leaveType->id, 'status' => 'pending']);
    OvertimeRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $reportEmployee->id, 'status' => 'pending']);
    OvertimeRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $strangerEmployee->id, 'status' => 'pending']);

    $response = $this->actingAs($managerUser)->get(route('inbox.index'));

    $response->assertOk();
    $response->assertSee($reportEmployee->fullName());
    $response->assertDontSee($strangerEmployee->fullName());
});

test('an hr admin sees every pending request in the tenant', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employeeA] = employeeUser($tenant, $entity, 'Employee');
    [$employeeB] = employeeUser($tenant, $entity, 'Employee');

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);
    LeaveRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $employeeA->id, 'leave_type_id' => $leaveType->id, 'status' => 'pending']);
    OvertimeRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $employeeB->id, 'status' => 'pending']);

    $response = $this->actingAs($admin)->get(route('inbox.index'));

    $response->assertOk();
    $response->assertSee($employeeA->fullName());
    $response->assertSee($employeeB->fullName());
});

test('an employee with no approval permissions sees an empty inbox', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [, $employeeUserAccount] = employeeUser($tenant, $entity, 'Employee');

    $response = $this->actingAs($employeeUserAccount)->get(route('inbox.index'));

    $response->assertOk();
    $response->assertSee('nothing needs your attention');
});
