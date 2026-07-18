<?php

use App\Actions\Attendance\ClockIn;
use App\Actions\Attendance\ClockOut;
use App\Actions\Attendance\RecomputeAttendanceDay;
use App\Models\AttendanceDay;
use App\Models\ClockEvent;
use App\Models\Entity;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Shift;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

test('clocking in via the web route creates an event and an attendance day', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $user] = employeeUser($tenant, $entity, 'Employee');

    $response = $this->actingAs($user)->post(route('attendance.clock-in'));

    $response->assertRedirect(route('attendance.index'));
    expect(ClockEvent::where('employee_id', $employee->id)->where('type', 'clock_in')->exists())->toBeTrue();

    $day = AttendanceDay::where('employee_id', $employee->id)->whereDate('date', now()->toDateString())->first();
    expect($day)->not->toBeNull();
    expect($day->clock_in_at)->not->toBeNull();
});

test('clocking out closes the day and computes worked minutes', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee] = employeeUser($tenant, $entity, 'Employee');

    Carbon::setTestNow(now()->setTime(8, 0));
    app(ClockIn::class)->handle($employee);

    Carbon::setTestNow(now()->addHours(8));
    app(ClockOut::class)->handle($employee);

    $day = AttendanceDay::where('employee_id', $employee->id)->first();
    expect($day->worked_minutes)->toBe(480);

    Carbon::setTestNow();
});

test('clocking in twice without clocking out is rejected', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee] = employeeUser($tenant, $entity, 'Employee');

    app(ClockIn::class)->handle($employee);

    expect(fn () => app(ClockIn::class)->handle($employee))
        ->toThrow(ValidationException::class);
});

test('clocking out without an open clock-in is rejected', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee] = employeeUser($tenant, $entity, 'Employee');

    expect(fn () => app(ClockOut::class)->handle($employee))
        ->toThrow(ValidationException::class);
});

test('a clock-in after the shift grace period is marked late', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee] = employeeUser($tenant, $entity, 'Employee');
    Shift::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'start_time' => '08:00', 'end_time' => '17:00']);

    Carbon::setTestNow(now()->setTime(9, 0));
    app(ClockIn::class)->handle($employee);

    $day = AttendanceDay::where('employee_id', $employee->id)->first();
    expect($day->status)->toBe('late');

    Carbon::setTestNow();
});

test('a day covered by an approved leave request is marked on_leave', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee] = employeeUser($tenant, $entity, 'Employee');
    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);

    LeaveRequest::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'leave_type_id' => $leaveType->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->toDateString(),
        'status' => 'approved',
    ]);

    $day = app(RecomputeAttendanceDay::class)->handle($employee, now());

    expect($day->status)->toBe('on_leave');
});
