<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;
use App\Models\JobRequisition;
use App\Models\Position;

test('hr admin can view the headcount by department report', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    employeeUser($tenant, $entity, 'Employee', reportsTo: employeeUser($tenant, $entity, 'Team Lead')[0]);

    $response = $this->actingAs($hrAdmin)->get(route('reports.headcount-by-department'));

    $response->assertOk();
});

test('hr admin can export a report as csv', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');

    $response = $this->actingAs($hrAdmin)->get(route('reports.headcount-by-department', ['format' => 'csv']));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

test('an employee cannot access reports', function () {
    [, $employeeUser] = tenantWithRole('Employee');

    $this->actingAs($employeeUser)->get(route('reports.index'))->assertForbidden();
});

test('a department manager cannot access reports either', function () {
    [, $deptManagerUser] = tenantWithRole('Department Manager');

    $this->actingAs($deptManagerUser)->get(route('reports.index'))->assertForbidden();
});

test('the reports gallery renders a tile per report, ordered for the acting role', function () {
    [, $hrAdmin] = tenantWithRole('HR Admin');

    $response = $this->actingAs($hrAdmin)->get(route('reports.index'));

    $response->assertOk();
    $response->assertSeeInOrder([
        'Headcount by department',
        'Recruitment pipeline',
        'Leave utilization',
        'Attendance summary',
        'Payroll cost summary',
    ]);
});

test('drilling into a department on the headcount report shows only that department\'s employees', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $otherDepartment = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'first_name' => 'Dana']);
    Employment::factory()->create([
        'tenant_id' => $tenant->id, 'employee_id' => $employee->id, 'entity_id' => $entity->id,
        'department_id' => $department->id, 'position_id' => $position->id,
    ]);

    $other = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'first_name' => 'Other']);
    Employment::factory()->create([
        'tenant_id' => $tenant->id, 'employee_id' => $other->id, 'entity_id' => $entity->id,
        'department_id' => $otherDepartment->id, 'position_id' => $position->id,
    ]);

    $response = $this->actingAs($hrAdmin)->get(route('reports.headcount-by-department', ['department_id' => $department->id]));

    $response->assertOk();
    $response->assertSee('Dana');
    $response->assertDontSee('Other');
});

test('an unknown department filter does not error', function () {
    [, $hrAdmin] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdmin)->get(route('reports.headcount-by-department', ['department_id' => 999999]))
        ->assertOk();
});

test('drilling into a stage on the recruitment pipeline shows only candidates in that stage', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $requisition = JobRequisition::create([
        'tenant_id' => $tenant->id, 'entity_id' => $entity->id, 'department_id' => $department->id,
        'position_id' => $position->id, 'title' => 'Backend Engineer', 'headcount' => 1, 'status' => 'open',
    ]);

    $requisition->candidates()->create([
        'tenant_id' => $tenant->id, 'first_name' => 'Aisha', 'last_name' => 'Nantongo',
        'email' => 'aisha@example.com', 'status' => 'interviews',
    ]);
    $requisition->candidates()->create([
        'tenant_id' => $tenant->id, 'first_name' => 'Brian', 'last_name' => 'Kato',
        'email' => 'brian@example.com', 'status' => 'advertising',
    ]);

    $response = $this->actingAs($hrAdmin)->get(route('reports.recruitment-pipeline', ['stage' => 'interviews']));

    $response->assertOk();
    $response->assertSee('Aisha Nantongo');
    $response->assertDontSee('Brian Kato');
});

test('an invalid pipeline stage filter is ignored safely', function () {
    [, $hrAdmin] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdmin)->get(route('reports.recruitment-pipeline', ['stage' => 'not-a-real-stage']))
        ->assertOk();
});
