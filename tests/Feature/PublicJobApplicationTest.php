<?php

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Entity;
use App\Models\JobRequisition;
use App\Models\Position;
use App\Models\Tenant;
use App\Models\TenantFeatureFlag;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

function openRequisition(Tenant $tenant, Entity $entity, Department $department): JobRequisition
{
    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    return JobRequisition::create([
        'tenant_id' => $tenant->id,
        'entity_id' => $entity->id,
        'department_id' => $department->id,
        'position_id' => $position->id,
        'title' => 'Backend Engineer',
        'headcount' => 1,
        'status' => 'open',
    ]);
}

test('a candidate can apply to an open job posting through the public api', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = openRequisition($tenant, $entity, $department);

    $response = $this->postJson(route('api.v1.public.job-requisitions.apply', $requisition), [
        'first_name' => 'Patricia',
        'last_name' => 'Nakato',
        'email' => 'patricia.nakato@example.com',
    ]);

    $response->assertCreated();
    $response->assertExactJson(['message' => 'Application received.']);

    $candidate = Candidate::where('email', 'patricia.nakato@example.com')->firstOrFail();
    expect($candidate->tenant_id)->toBe($tenant->id);
    expect($candidate->job_requisition_id)->toBe($requisition->id);
    expect($candidate->status)->toBe('applied');
    expect($candidate->source)->toBe('Careers portal');
});

test('a supplied source overrides the default', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = openRequisition($tenant, $entity, $department);

    $this->postJson(route('api.v1.public.job-requisitions.apply', $requisition), [
        'first_name' => 'Q', 'last_name' => 'R', 'email' => 'q@example.com', 'source' => 'LinkedIn',
    ])->assertCreated();

    expect(Candidate::where('email', 'q@example.com')->firstOrFail()->source)->toBe('LinkedIn');
});

test('an applicant can attach a resume', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = openRequisition($tenant, $entity, $department);

    $this->postJson(route('api.v1.public.job-requisitions.apply', $requisition), [
        'first_name' => 'S', 'last_name' => 'T', 'email' => 's@example.com',
        'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
    ])->assertCreated();

    $candidate = Candidate::where('email', 's@example.com')->firstOrFail();
    expect($candidate->resume_path)->not->toBeNull();
    Storage::disk('local')->assertExists($candidate->resume_path);
});

test('applying twice with the same email to the same job is rejected', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = openRequisition($tenant, $entity, $department);

    $payload = ['first_name' => 'U', 'last_name' => 'V', 'email' => 'u@example.com'];
    $this->postJson(route('api.v1.public.job-requisitions.apply', $requisition), $payload)->assertCreated();

    $this->postJson(route('api.v1.public.job-requisitions.apply', $requisition), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect(Candidate::where('email', 'u@example.com')->count())->toBe(1);
});

test('the same email can apply to a different job posting', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisitionA = openRequisition($tenant, $entity, $department);
    $requisitionB = openRequisition($tenant, $entity, $department);

    $payload = ['first_name' => 'W', 'last_name' => 'X', 'email' => 'w@example.com'];
    $this->postJson(route('api.v1.public.job-requisitions.apply', $requisitionA), $payload)->assertCreated();
    $this->postJson(route('api.v1.public.job-requisitions.apply', $requisitionB), $payload)->assertCreated();

    expect(Candidate::where('email', 'w@example.com')->count())->toBe(2);
});

test('applying to a non-open requisition is rejected', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = openRequisition($tenant, $entity, $department);
    $requisition->update(['status' => 'closed']);

    $this->postJson(route('api.v1.public.job-requisitions.apply', $requisition), [
        'first_name' => 'Y', 'last_name' => 'Z', 'email' => 'y@example.com',
    ])->assertNotFound();
});

test('applying to a nonexistent job requisition 404s', function () {
    $this->postJson('/api/v1/public/job-requisitions/999999/apply', [
        'first_name' => 'A', 'last_name' => 'B', 'email' => 'nowhere@example.com',
    ])->assertNotFound();
});

test('applying against a suspended tenant is rejected', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $tenant->update(['status' => 'suspended']);
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = openRequisition($tenant, $entity, $department);

    $this->postJson(route('api.v1.public.job-requisitions.apply', $requisition), [
        'first_name' => 'C', 'last_name' => 'D', 'email' => 'suspended@example.com',
    ])->assertNotFound();

    expect(Candidate::where('email', 'suspended@example.com')->exists())->toBeFalse();
});

test('applying when the recruitment module is disabled is rejected', function () {
    [$tenant] = tenantWithRole('HR Admin');
    TenantFeatureFlag::create(['tenant_id' => $tenant->id, 'module' => 'recruitment', 'enabled' => false]);
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = openRequisition($tenant, $entity, $department);

    $this->postJson(route('api.v1.public.job-requisitions.apply', $requisition), [
        'first_name' => 'E', 'last_name' => 'F', 'email' => 'disabled@example.com',
    ])->assertNotFound();
});

test('missing required fields are rejected', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $requisition = openRequisition($tenant, $entity, $department);

    $this->postJson(route('api.v1.public.job-requisitions.apply', $requisition), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
});
