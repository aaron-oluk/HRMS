<?php

use App\Models\Entity;

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
