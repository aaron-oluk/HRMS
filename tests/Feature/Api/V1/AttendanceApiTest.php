<?php

use App\Models\AttendanceDay;
use App\Models\ClockEvent;
use App\Models\Entity;
use App\Models\OvertimeRequest;
use Laravel\Sanctum\Sanctum;

test('an employee can clock in and out through the api', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $user] = employeeUser($tenant, $entity, 'Employee');

    Sanctum::actingAs($user);

    $this->postJson('/api/v1/attendance/clock-in')->assertCreated();
    expect(ClockEvent::where('employee_id', $employee->id)->where('type', 'clock_in')->exists())->toBeTrue();

    $this->postJson('/api/v1/attendance/clock-out')->assertNoContent();
    expect(ClockEvent::where('employee_id', $employee->id)->where('type', 'clock_out')->exists())->toBeTrue();

    $day = AttendanceDay::where('employee_id', $employee->id)->first();
    expect($day->clock_out_at)->not->toBeNull();
});

test('a manager sees only their team in the attendance-today endpoint', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$reportEmployee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $managerEmployee);
    [$strangerEmployee] = employeeUser($tenant, $entity, 'Employee');

    $teamDay = AttendanceDay::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $reportEmployee->id, 'date' => now()->toDateString()]);
    AttendanceDay::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $strangerEmployee->id, 'date' => now()->toDateString()]);

    Sanctum::actingAs($managerUser);

    $response = $this->getJson('/api/v1/attendance/team-today')->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($teamDay->id);
    expect($ids)->toHaveCount(1);
});

test('an employee can submit an overtime request through the api', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $user] = employeeUser($tenant, $entity, 'Employee');

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/overtime-requests', [
        'date' => now()->toDateString(),
        'hours' => 2.5,
        'reason' => 'API test',
    ]);

    $response->assertCreated();
    expect(OvertimeRequest::where('employee_id', $employee->id)->exists())->toBeTrue();
});

test('approving an overtime request through the api updates status and approver', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$managerEmployee, $managerUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$reportEmployee] = employeeUser($tenant, $entity, 'Employee', reportsTo: $managerEmployee);

    $request = OvertimeRequest::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $reportEmployee->id, 'status' => 'pending']);

    Sanctum::actingAs($managerUser);

    $this->postJson("/api/v1/overtime-requests/{$request->id}/approve")->assertOk();

    expect($request->fresh()->status)->toBe('approved');
    expect($request->fresh()->approved_by)->toBe($managerUser->id);
});

test('a tenant cannot approve another tenant\'s overtime request through the api', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    [$employeeA] = employeeUser($tenantA, $entityA, 'Employee');
    $requestA = OvertimeRequest::factory()->create(['tenant_id' => $tenantA->id, 'employee_id' => $employeeA->id, 'status' => 'pending']);

    [$tenantB, $adminB] = tenantWithRole('HR Admin');
    Sanctum::actingAs($adminB);

    $this->postJson("/api/v1/overtime-requests/{$requestA->id}/approve")->assertNotFound();
});
