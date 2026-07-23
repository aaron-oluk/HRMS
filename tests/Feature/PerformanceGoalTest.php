<?php

use App\Models\Entity;
use App\Models\PerformanceGoal;

test('an employee can create and update their own goal', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $employeeUser] = employeeUser($tenant, $entity, 'Employee');

    $this->actingAs($employeeUser)->post(route('performance.goals.store'), [
        'title' => 'Learn Terraform',
        'status' => 'on_track',
    ])->assertRedirect();

    $goal = $employee->performanceGoals()->firstOrFail();
    expect($goal->title)->toBe('Learn Terraform');

    $this->actingAs($employeeUser)->put(route('performance.goals.update', $goal), [
        'title' => 'Learn Terraform',
        'status' => 'completed',
    ])->assertRedirect();

    expect($goal->fresh()->status)->toBe('completed');
});

test('a qualitative goal can be created with a description and timeline but no numeric target', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $employeeUser] = employeeUser($tenant, $entity, 'Employee');

    $this->actingAs($employeeUser)->post(route('performance.goals.store'), [
        'title' => 'Improve public speaking confidence',
        'description' => 'Present at least once at the monthly team all-hands.',
        'start_date' => now()->toDateString(),
        'due_date' => now()->addMonths(6)->toDateString(),
        'status' => 'on_track',
    ])->assertRedirect();

    $goal = $employee->performanceGoals()->firstOrFail();
    expect($goal->description)->toBe('Present at least once at the monthly team all-hands.');
    expect($goal->start_date)->not->toBeNull();
    expect($goal->due_date)->not->toBeNull();
    expect($goal->target_value)->toBeNull();
    expect($goal->unit)->toBeNull();
});

test('a goal\'s due date cannot be before its start date', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [, $employeeUser] = employeeUser($tenant, $entity, 'Employee');

    $this->actingAs($employeeUser)->post(route('performance.goals.store'), [
        'title' => 'Backwards timeline',
        'start_date' => now()->addMonths(3)->toDateString(),
        'due_date' => now()->toDateString(),
        'status' => 'on_track',
    ])->assertSessionHasErrors('due_date');
});

test('a goal\'s description and timeline render on the performance page', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employee, $employeeUser] = employeeUser($tenant, $entity, 'Employee');

    PerformanceGoal::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'title' => 'Mentor a junior engineer',
        'description' => 'Weekly 30-minute pairing sessions.',
        'target_value' => null,
        'current_value' => null,
        'unit' => null,
        'start_date' => '2026-01-01',
        'due_date' => '2026-06-30',
    ]);

    $response = $this->actingAs($employeeUser)->get(route('performance.my'));

    $response->assertOk();
    $response->assertSee('Weekly 30-minute pairing sessions.');
    $response->assertSee('01 Jan 2026', false);
});

test('an employee cannot update another employee\'s goal', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$employeeA, $userA] = employeeUser($tenant, $entity, 'Employee');
    [, $userB] = employeeUser($tenant, $entity, 'Employee');

    $goal = $employeeA->performanceGoals()->create(['tenant_id' => $tenant->id, 'title' => 'Ship the migration', 'status' => 'on_track']);

    $this->actingAs($userB)->put(route('performance.goals.update', $goal), [
        'title' => 'Hijacked',
        'status' => 'completed',
    ])->assertForbidden();

    expect($goal->fresh()->title)->toBe('Ship the migration');
});
