<?php

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Entity;

test('creating an employee writes one audit row per recorded field', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $logs = AuditLog::where('auditable_type', Employee::class)
        ->where('auditable_id', $employee->id)
        ->where('action', 'created')
        ->get();

    expect($logs)->not->toBeEmpty();
    expect($logs->pluck('field'))->toContain('first_name');
    expect($logs->every(fn ($log) => $log->actor_id === $admin->id))->toBeTrue();
});

test('updating a field writes an audit row with the old and new value', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id, 'phone' => '+256700000000']);

    $this->actingAs($admin);
    $employee->update(['phone' => '+256711111111']);

    $log = AuditLog::where('auditable_type', Employee::class)
        ->where('auditable_id', $employee->id)
        ->where('action', 'updated')
        ->where('field', 'phone')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->old_value)->toBe('+256700000000');
    expect($log->new_value)->toBe('+256711111111');
});

test('audit log rows cannot be updated or deleted', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $log = AuditLog::where('auditable_type', Employee::class)->where('auditable_id', $employee->id)->firstOrFail();

    expect(fn () => $log->update(['new_value' => 'tampered']))->toThrow(LogicException::class);
    expect(fn () => $log->delete())->toThrow(LogicException::class);
});
