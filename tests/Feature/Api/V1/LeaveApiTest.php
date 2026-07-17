<?php

use App\Models\Entity;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Laravel\Sanctum\Sanctum;

test('an employee can submit a leave request through the api', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $user] = employeeUser($tenant, $entity, 'Employee');
    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'default_days_per_year' => 21]);

    Sanctum::actingAs($user);

    $monday = now()->next('Monday');

    $response = $this->postJson('/api/v1/leave-requests', [
        'leave_type_id' => $leaveType->id,
        'start_date' => $monday->toDateString(),
        'end_date' => $monday->copy()->addDays(1)->toDateString(),
        'reason' => 'API test',
    ]);

    $response->assertCreated();
    expect(LeaveRequest::where('employee_id', $employee->id)->exists())->toBeTrue();
});

test('a manager sees only their team\'s pending requests in the approvals queue', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Manager');
    [$reportEmployee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $managerEmployee);
    [$strangerEmployee] = employeeUser($tenant, $entity, 'Employee');

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);
    $teamRequest = LeaveRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $reportEmployee->id,
        'leave_type_id' => $leaveType->id,
        'status' => 'pending',
    ]);
    LeaveRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $strangerEmployee->id,
        'leave_type_id' => $leaveType->id,
        'status' => 'pending',
    ]);

    Sanctum::actingAs($managerUser);

    $response = $this->getJson('/api/v1/leave-approvals')->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($teamRequest->id);
    expect($ids)->toHaveCount(1);
});

test('approving through the api updates status and approver', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Manager');
    [$reportEmployee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $managerEmployee);

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);
    $request = LeaveRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $reportEmployee->id,
        'leave_type_id' => $leaveType->id,
        'status' => 'pending',
    ]);

    Sanctum::actingAs($managerUser);

    $this->postJson("/api/v1/leave-requests/{$request->id}/approve")->assertOk();

    expect($request->fresh()->status)->toBe('approved');
    expect($request->fresh()->approved_by)->toBe($managerUser->id);
});

test('a tenant cannot approve another tenant\'s leave request through the api', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    [$employeeA] = employeeUser($tenantA, $entityA, 'Employee');
    $typeA = LeaveType::factory()->create(['tenant_id' => $tenantA->id, 'entity_id' => $entityA->id]);
    $requestA = LeaveRequest::factory()->create([
        'tenant_id' => $tenantA->id,
        'employee_id' => $employeeA->id,
        'leave_type_id' => $typeA->id,
        'status' => 'pending',
    ]);

    [$tenantB, $adminB] = tenantWithRole('HR Admin');
    Sanctum::actingAs($adminB);

    $this->postJson("/api/v1/leave-requests/{$requestA->id}/approve")->assertNotFound();
});
