<?php

use App\Actions\Leave\SubmitLeaveRequest;
use App\Models\AttendanceDay;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function employeeInDepartment(Tenant $tenant, Entity $entity, Department $department): Employee
{
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    Employment::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'entity_id' => $entity->id,
        'department_id' => $department->id,
        'position_id' => $position->id,
    ]);

    return $employee;
}

function submitLeaveFor(Tenant $tenant, Entity $entity, Employee $employee): LeaveRequest
{
    $leaveType = LeaveType::factory()->create(['tenant_id' => $tenant->id, 'entity_id' => $entity->id]);
    $monday = now()->next('Monday');

    return app(SubmitLeaveRequest::class)->handle($employee, [
        'leave_type_id' => $leaveType->id,
        'start_date' => $monday->toDateString(),
        'end_date' => $monday->copy()->addDays(2)->toDateString(),
        'reason' => 'Test',
    ]);
}

test('a department manager can approve leave for employees in their own department only', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $financeDept = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $engineeringDept = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $managerEmployee = employeeInDepartment($tenant, $entity, $financeDept);
    $sameDeptEmployee = employeeInDepartment($tenant, $entity, $financeDept);
    $otherDeptEmployee = employeeInDepartment($tenant, $entity, $engineeringDept);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $role = Role::where('tenant_id', $tenant->id)->where('name', 'Department Manager')->firstOrFail();
    $managerUser = User::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $managerEmployee->id]);
    $managerUser->assignRole($role);

    $sameDeptRequest = submitLeaveFor($tenant, $entity, $sameDeptEmployee);
    $otherDeptRequest = submitLeaveFor($tenant, $entity, $otherDeptEmployee);

    $this->actingAs($managerUser)->post(route('leave.approve', $sameDeptRequest));
    expect($sameDeptRequest->fresh()->status)->toBe('approved');

    $this->actingAs($managerUser)->post(route('leave.approve', $otherDeptRequest));
    expect($otherDeptRequest->fresh()->status)->toBe('pending');
});

test('a department manager sees only their department in the attendance-today endpoint', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $financeDept = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $engineeringDept = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $managerEmployee = employeeInDepartment($tenant, $entity, $financeDept);
    $sameDeptEmployee = employeeInDepartment($tenant, $entity, $financeDept);
    $otherDeptEmployee = employeeInDepartment($tenant, $entity, $engineeringDept);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $role = Role::where('tenant_id', $tenant->id)->where('name', 'Department Manager')->firstOrFail();
    $managerUser = User::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $managerEmployee->id]);
    $managerUser->assignRole($role);

    $teamDay = AttendanceDay::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $sameDeptEmployee->id, 'date' => now()->toDateString()]);
    AttendanceDay::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $otherDeptEmployee->id, 'date' => now()->toDateString()]);

    Sanctum::actingAs($managerUser);

    $response = $this->getJson('/api/v1/attendance/team-today')->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($teamDay->id);
    expect($ids)->toHaveCount(1);
});

test('a team lead is still limited to direct reports, not their whole department', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $teamLeadEmployee = employeeInDepartment($tenant, $entity, $department);
    $peerEmployee = employeeInDepartment($tenant, $entity, $department);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $role = Role::where('tenant_id', $tenant->id)->where('name', 'Team Lead')->firstOrFail();
    $teamLeadUser = User::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $teamLeadEmployee->id]);
    $teamLeadUser->assignRole($role);

    $peerRequest = submitLeaveFor($tenant, $entity, $peerEmployee);

    $this->actingAs($teamLeadUser)->post(route('leave.approve', $peerRequest));

    expect($peerRequest->fresh()->status)->toBe('pending');
});
