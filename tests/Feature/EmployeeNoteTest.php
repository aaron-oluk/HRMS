<?php

use App\Models\Employee;
use App\Models\EmployeeNote;
use App\Models\Entity;

test('an hr admin can add an internal note to an employee', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->post(route('employees.notes.store', $employee), [
        'title' => 'Promotion feedback',
        'body' => 'Consistently exceeds expectations.',
    ])->assertRedirect(route('employees.show', $employee));

    $note = EmployeeNote::where('employee_id', $employee->id)->firstOrFail();
    expect($note->title)->toBe('Promotion feedback');
    expect($note->tenant_id)->toBe($tenant->id);
});

test('an hr admin can remove an internal note', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $note = EmployeeNote::factory()->for($employee)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->delete(route('employees.notes.destroy', [$employee, $note]))
        ->assertRedirect(route('employees.show', $employee));

    expect(EmployeeNote::find($note->id))->toBeNull();
});

test('an auditor can view but not add internal notes', function () {
    [$tenant, $auditor] = tenantWithRole('Auditor');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    EmployeeNote::factory()->for($employee)->create(['tenant_id' => $tenant->id, 'title' => 'Appreciation note']);

    $response = $this->actingAs($auditor)->get(route('employees.show', $employee))->assertOk();
    $response->assertSee('Appreciation note');

    $this->actingAs($auditor)->post(route('employees.notes.store', $employee), [
        'title' => 'Nope', 'body' => 'Nope',
    ])->assertForbidden();
});

test('a team lead cannot view internal notes', function () {
    [$tenant, $teamLead] = tenantWithRole('Team Lead');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    EmployeeNote::factory()->for($employee)->create(['tenant_id' => $tenant->id, 'title' => 'Confidential note']);

    $response = $this->actingAs($teamLead)->get(route('employees.show', $employee))->assertOk();
    $response->assertDontSee('Confidential note');
});
