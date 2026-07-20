<?php

use App\Models\Department;
use App\Models\Entity;

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
