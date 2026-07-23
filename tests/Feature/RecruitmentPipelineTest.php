<?php

use App\Models\CandidateComment;
use App\Models\Department;
use App\Models\Entity;

test('the pipeline groups candidates by stage and respects the job filter', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisitionA = makeRequisition($tenant, $entity, $department);
    $requisitionB = makeRequisition($tenant, $entity, $department);
    $requisitionB->update(['title' => 'Frontend Engineer']);

    $requisitionA->candidates()->create(['tenant_id' => $tenant->id, 'first_name' => 'Aisha', 'last_name' => 'Nantongo', 'email' => 'aisha@example.com', 'status' => 'advertising']);
    $requisitionB->candidates()->create(['tenant_id' => $tenant->id, 'first_name' => 'Brian', 'last_name' => 'Kato', 'email' => 'brian@example.com', 'status' => 'interviews']);

    $response = $this->actingAs($hrAdmin)->get(route('recruitment.pipeline'));
    $response->assertOk();
    $response->assertSee('Aisha Nantongo');
    $response->assertSee('Brian Kato');

    $filtered = $this->actingAs($hrAdmin)->get(route('recruitment.pipeline', ['job_requisition_id' => $requisitionA->id]));
    $filtered->assertOk();
    $filtered->assertSee('Aisha Nantongo');
    $filtered->assertDontSee('Brian Kato');
});

test('an employee cannot access the pipeline', function () {
    [, $employeeUser] = tenantWithRole('Employee');

    $this->actingAs($employeeUser)->get(route('recruitment.pipeline'))->assertForbidden();
});

test('a department manager only sees pipeline candidates for their own department', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $ownDepartment = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $otherDepartment = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    [$deptManagerEmployee, $deptManagerUser] = departmentManagerIn($tenant, $entity, $ownDepartment);

    $ownRequisition = makeRequisition($tenant, $entity, $ownDepartment);
    $otherRequisition = makeRequisition($tenant, $entity, $otherDepartment);

    $ownRequisition->candidates()->create(['tenant_id' => $tenant->id, 'first_name' => 'Carol', 'last_name' => 'Auma', 'email' => 'carol@example.com']);
    $otherRequisition->candidates()->create(['tenant_id' => $tenant->id, 'first_name' => 'Derek', 'last_name' => 'Okot', 'email' => 'derek@example.com']);

    $response = $this->actingAs($deptManagerUser)->get(route('recruitment.pipeline'));
    $response->assertOk();
    $response->assertSee('Carol Auma');
    $response->assertDontSee('Derek Okot');
});

test('an hr admin can add a candidate from the pipeline board', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = makeRequisition($tenant, $entity, $department);

    $this->actingAs($hrAdmin)->post(route('recruitment.candidates.store'), [
        'job_requisition_id' => $requisition->id,
        'first_name' => 'Grace',
        'last_name' => 'Namono',
        'email' => 'grace@example.com',
    ])->assertRedirect(route('recruitment.pipeline'));

    expect($requisition->candidates()->where('email', 'grace@example.com')->exists())->toBeTrue();
});

test('a candidate can be viewed, advanced, and rejected from the show page', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = makeRequisition($tenant, $entity, $department);
    $candidate = $requisition->candidates()->create([
        'tenant_id' => $tenant->id, 'first_name' => 'Ivan', 'last_name' => 'Mugisha',
        'email' => 'ivan@example.com', 'status' => 'advertising',
    ]);

    $this->actingAs($hrAdmin)->get(route('recruitment.candidates.show', $candidate))
        ->assertOk()
        ->assertSee('Ivan Mugisha')
        ->assertSee('ivan@example.com');

    expect($candidate->nextStatus())->toBe('review');

    $this->actingAs($hrAdmin)->post(route('recruitment.requisitions.candidates.stage', [$requisition, $candidate]), [
        'status' => $candidate->nextStatus(),
    ])->assertRedirect();

    expect($candidate->fresh()->status)->toBe('review');

    $this->actingAs($hrAdmin)->post(route('recruitment.requisitions.candidates.stage', [$requisition, $candidate]), [
        'status' => 'rejected',
    ])->assertRedirect();

    expect($candidate->fresh()->status)->toBe('rejected');
});

test('nextStatus is null once rejected or once at the final probation stage', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = makeRequisition($tenant, $entity, $department);

    $rejected = $requisition->candidates()->create(['tenant_id' => $tenant->id, 'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@example.com', 'status' => 'rejected']);
    $atFinalStage = $requisition->candidates()->create(['tenant_id' => $tenant->id, 'first_name' => 'C', 'last_name' => 'D', 'email' => 'c@example.com', 'status' => 'probation']);

    expect($rejected->nextStatus())->toBeNull();
    expect($atFinalStage->nextStatus())->toBeNull();
});

test('an hr admin can comment on a candidate and remove their own comment', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = makeRequisition($tenant, $entity, $department);
    $candidate = $requisition->candidates()->create(['tenant_id' => $tenant->id, 'first_name' => 'Emma', 'last_name' => 'Achieng', 'email' => 'emma@example.com']);

    $this->actingAs($hrAdmin)->post(route('recruitment.candidates.comments.store', $candidate), [
        'body' => 'Strong technical interview.',
    ])->assertRedirect(route('recruitment.candidates.show', $candidate));

    $comment = CandidateComment::where('candidate_id', $candidate->id)->firstOrFail();
    expect($comment->body)->toBe('Strong technical interview.');
    expect($comment->created_by)->toBe($hrAdmin->id);

    $this->actingAs($hrAdmin)->delete(route('recruitment.candidates.comments.destroy', [$candidate, $comment]))
        ->assertRedirect(route('recruitment.candidates.show', $candidate));

    expect(CandidateComment::find($comment->id))->toBeNull();
});

test('an auditor (view-only) cannot comment on candidates', function () {
    [$tenant, $auditor] = tenantWithRole('Auditor');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = makeRequisition($tenant, $entity, $department);
    $candidate = $requisition->candidates()->create(['tenant_id' => $tenant->id, 'first_name' => 'F', 'last_name' => 'G', 'email' => 'f@example.com']);

    $this->actingAs($auditor)->post(route('recruitment.candidates.comments.store', $candidate), [
        'body' => 'Nope',
    ])->assertForbidden();
});

test('a candidate can be rated 1 through 5, and an invalid rating is rejected', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = makeRequisition($tenant, $entity, $department);
    $candidate = $requisition->candidates()->create(['tenant_id' => $tenant->id, 'first_name' => 'H', 'last_name' => 'I', 'email' => 'h@example.com']);

    $this->actingAs($hrAdmin)->post(route('recruitment.candidates.rate', $candidate), ['rating' => 4])
        ->assertRedirect();

    expect($candidate->fresh()->rating)->toBe(4);

    $this->actingAs($hrAdmin)->post(route('recruitment.candidates.rate', $candidate), ['rating' => 6])
        ->assertSessionHasErrors('rating');
});

test('a candidate from another tenant is invisible on both the pipeline and the show page', function () {
    [$tenantA, $hrAdminA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    $departmentA = Department::factory()->for($entityA)->create(['tenant_id' => $tenantA->id]);
    $requisitionA = makeRequisition($tenantA, $entityA, $departmentA);
    $candidateA = $requisitionA->candidates()->create(['tenant_id' => $tenantA->id, 'first_name' => 'J', 'last_name' => 'K', 'email' => 'j@example.com']);

    [, $hrAdminB] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdminB)->get(route('recruitment.candidates.show', $candidateA))->assertNotFound();
    $this->actingAs($hrAdminB)->get(route('recruitment.pipeline'))->assertOk()->assertDontSee('J K');
});
