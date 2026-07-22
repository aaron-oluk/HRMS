<?php

use App\Models\Employee;
use App\Models\EmployeeWorkExperience;
use App\Models\Entity;

test('an hr manager can add prior work experience to an employee', function () {
    [$tenant, $manager] = tenantWithRole('HR Manager');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($manager)->post(route('employees.work-experiences.store', $employee), [
        'company_name' => 'Kampala Tech Solutions',
        'job_title' => 'Junior Developer',
        'start_date' => now()->subYears(4)->toDateString(),
        'end_date' => now()->subYears(2)->toDateString(),
    ])->assertRedirect(route('employees.show', $employee));

    $experience = EmployeeWorkExperience::where('employee_id', $employee->id)->firstOrFail();
    expect($experience->company_name)->toBe('Kampala Tech Solutions');
    expect($experience->tenant_id)->toBe($tenant->id);
});

test('an hr manager can remove prior work experience', function () {
    [$tenant, $manager] = tenantWithRole('HR Manager');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $experience = EmployeeWorkExperience::factory()->for($employee)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($manager)->delete(route('employees.work-experiences.destroy', [$employee, $experience]))
        ->assertRedirect(route('employees.show', $employee));

    expect(EmployeeWorkExperience::find($experience->id))->toBeNull();
});

test('an hr specialist cannot manage prior work experience', function () {
    [$tenant, $specialist] = tenantWithRole('HR Specialist');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($specialist)->post(route('employees.work-experiences.store', $employee), [
        'company_name' => 'Nope Ltd',
        'job_title' => 'Nope',
        'start_date' => now()->subYear()->toDateString(),
    ])->assertForbidden();
});

test('total experience combines prior experience with current employment tenure', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    EmployeeWorkExperience::factory()->for($employee)->create([
        'tenant_id' => $tenant->id,
        'start_date' => now()->subYears(3)->toDateString(),
        'end_date' => now()->subYears(2)->toDateString(),
    ]);

    expect($employee->totalExperienceMonths())->toBe(12);
    expect($employee->totalExperienceLabel())->toBe('1 yr');
});
