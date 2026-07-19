<?php

use App\Models\Department;
use App\Models\Employment;
use App\Models\Entity;
use App\Models\JobRequisition;
use App\Models\Position;
use App\Models\Tenant;

/**
 * employeeUser() only creates an Employment when a $reportsTo is passed, but a Department
 * Manager's department-scoping keys off Employment.department_id regardless of reporting
 * line — so give them one explicitly here.
 */
function departmentManagerIn(Tenant $tenant, Entity $entity, Department $department): array
{
    [$employee, $user] = employeeUser($tenant, $entity, 'Department Manager');

    Employment::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'entity_id' => $entity->id,
        'department_id' => $department->id,
    ]);

    return [$employee->fresh(), $user];
}

function makeRequisition(Tenant $tenant, Entity $entity, Department $department): JobRequisition
{
    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    return JobRequisition::create([
        'tenant_id' => $tenant->id,
        'entity_id' => $entity->id,
        'department_id' => $department->id,
        'position_id' => $position->id,
        'title' => 'Backend Engineer',
        'headcount' => 2,
        'status' => 'open',
    ]);
}

test('hr admin can create a requisition and add a candidate', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($hrAdmin)->post(route('recruitment.requisitions.store'), [
        'entity_id' => $entity->id,
        'department_id' => $department->id,
        'position_id' => $position->id,
        'title' => 'Backend Engineer',
        'headcount' => 2,
        'status' => 'open',
    ])->assertRedirect();

    $requisition = JobRequisition::where('title', 'Backend Engineer')->firstOrFail();

    $this->actingAs($hrAdmin)->post(route('recruitment.requisitions.candidates.store', $requisition), [
        'first_name' => 'Aisha',
        'last_name' => 'Nantongo',
        'email' => 'aisha@example.com',
    ])->assertRedirect();

    expect($requisition->candidates()->count())->toBe(1);
});

test('a department manager only sees requisitions for their own department', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $ownDepartment = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    [$deptManagerEmployee, $deptManagerUser] = departmentManagerIn($tenant, $entity, $ownDepartment);

    $otherDepartment = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $ownRequisition = makeRequisition($tenant, $entity, $ownDepartment);
    $otherRequisition = makeRequisition($tenant, $entity, $otherDepartment);

    $otherRequisition->update(['title' => 'Frontend Engineer']);

    $response = $this->actingAs($deptManagerUser)->get(route('recruitment.requisitions.index'));
    $response->assertOk();
    $response->assertSee($ownRequisition->title);
    $response->assertDontSee('Frontend Engineer');

    $this->actingAs($deptManagerUser)->get(route('recruitment.requisitions.show', $otherRequisition))->assertForbidden();
});

test('only recruitment.view-candidate-pii holders see candidate contact details', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = makeRequisition($tenant, $entity, $department);
    $requisition->candidates()->create([
        'tenant_id' => $tenant->id,
        'first_name' => 'Aisha',
        'last_name' => 'Nantongo',
        'email' => 'aisha.private@example.com',
    ]);

    // HR Admin holds recruitment.view-candidate-pii.
    $this->actingAs($hrAdmin)->get(route('recruitment.requisitions.show', $requisition))
        ->assertSee('aisha.private@example.com');

    // Department Manager holds recruitment.view but not the PII permission.
    [$deptManagerEmployee, $deptManagerUser] = departmentManagerIn($tenant, $entity, $department);

    $this->actingAs($deptManagerUser)->get(route('recruitment.requisitions.show', $requisition))
        ->assertDontSee('aisha.private@example.com');
});

test('a candidate can be moved through the pipeline', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = makeRequisition($tenant, $entity, $department);
    $candidate = $requisition->candidates()->create([
        'tenant_id' => $tenant->id,
        'first_name' => 'Brian',
        'last_name' => 'Kato',
        'email' => 'brian@example.com',
    ]);

    $this->actingAs($hrAdmin)->post(route('recruitment.requisitions.candidates.stage', [$requisition, $candidate]), [
        'status' => 'interview',
    ])->assertRedirect();

    expect($candidate->fresh()->status)->toBe('interview');
});

test('a requisition from another tenant is invisible via the global scope', function () {
    [$tenantA, $hrAdminA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    $departmentA = Department::factory()->for($entityA)->create(['tenant_id' => $tenantA->id]);
    $requisitionA = makeRequisition($tenantA, $entityA, $departmentA);

    [, $hrAdminB] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdminB)->get(route('recruitment.requisitions.show', $requisitionA))->assertNotFound();
});
