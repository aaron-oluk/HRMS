<?php

use App\Models\Entity;
use App\Models\LeaveRequest;
use App\Models\LeaveType;

test('an employee can submit a leave request within their balance', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $user] = employeeUser($tenant, $entity, 'Employee');

    $leaveType = LeaveType::factory()->create([
        'tenant_id' => $tenant->id,
        'entity_id' => $entity->id,
        'default_days_per_year' => 21,
        'requires_approval' => true,
    ]);

    $monday = now()->next('Monday');

    $response = $this->actingAs($user)->post(route('leave.store'), [
        'leave_type_id' => $leaveType->id,
        'start_date' => $monday->toDateString(),
        'end_date' => $monday->copy()->addDays(2)->toDateString(),
        'reason' => 'Personal',
    ]);

    $response->assertRedirect(route('leave.index'));
    $request = LeaveRequest::where('employee_id', $employee->id)->first();
    expect($request)->not->toBeNull();
    expect((float) $request->days)->toBe(3.0);
    expect($request->status)->toBe('pending');
});

test('weekends are excluded from the requested day count', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $user] = employeeUser($tenant, $entity, 'Employee');

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'default_days_per_year' => 21]);

    $friday = now()->next('Friday');

    $this->actingAs($user)->post(route('leave.store'), [
        'leave_type_id' => $leaveType->id,
        'start_date' => $friday->toDateString(),
        'end_date' => $friday->copy()->addDays(3)->toDateString(), // Fri, Sat, Sun, Mon
        'reason' => 'Long weekend',
    ]);

    $request = LeaveRequest::where('employee_id', $employee->id)->first();
    expect((float) $request->days)->toBe(2.0);
});

test('a request exceeding the available balance is rejected', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $user] = employeeUser($tenant, $entity, 'Employee');

    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'default_days_per_year' => 2]);

    $monday = now()->next('Monday');

    $response = $this->actingAs($user)->post(route('leave.store'), [
        'leave_type_id' => $leaveType->id,
        'start_date' => $monday->toDateString(),
        'end_date' => $monday->copy()->addDays(4)->toDateString(),
        'reason' => 'Too much',
    ]);

    $response->assertSessionHasErrors('start_date');
    expect(LeaveRequest::where('employee_id', $employee->id)->exists())->toBeFalse();
});

test('a leave type that does not require approval auto-approves the request', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $user] = employeeUser($tenant, $entity, 'Employee');

    $leaveType = LeaveType::factory()->create([
        'tenant_id' => $tenant->id,
        'entity_id' => $entity->id,
        'requires_approval' => false,
    ]);

    $monday = now()->next('Monday');

    $this->actingAs($user)->post(route('leave.store'), [
        'leave_type_id' => $leaveType->id,
        'start_date' => $monday->toDateString(),
        'end_date' => $monday->toDateString(),
    ]);

    $request = LeaveRequest::where('employee_id', $employee->id)->first();
    expect($request->status)->toBe('approved');
});
