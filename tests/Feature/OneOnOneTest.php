<?php

use App\Models\Entity;

test('a manager can schedule a 1-on-1 with their direct report and log notes', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [$boss, $bossUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$report] = employeeUser($tenant, $entity, 'Employee', reportsTo: $boss);

    $this->actingAs($bossUser)->post(route('performance.one-on-ones.store'), [
        'employee_id' => $report->id,
        'scheduled_at' => now()->addWeek()->toDateTimeString(),
        'agenda' => 'Career growth',
    ])->assertRedirect();

    $meeting = $report->oneOnOnes()->firstOrFail();
    expect($meeting->manager_employee_id)->toBe($boss->id);
    expect($meeting->status)->toBe('scheduled');

    $this->actingAs($bossUser)->post(route('performance.one-on-ones.notes', $meeting), [
        'notes' => 'Discussed promotion timeline.',
    ])->assertRedirect();

    expect($meeting->fresh()->status)->toBe('completed');
    expect($meeting->fresh()->notes)->toBe('Discussed promotion timeline.');
});

test('a manager cannot schedule a 1-on-1 with an employee outside their team', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    [, $bossUser] = employeeUser($tenant, $entity, 'Team Lead');
    [$stranger] = employeeUser($tenant, $entity, 'Employee'); // no reportsTo

    $this->actingAs($bossUser)->post(route('performance.one-on-ones.store'), [
        'employee_id' => $stranger->id,
        'scheduled_at' => now()->addWeek()->toDateTimeString(),
    ])->assertForbidden();
});
