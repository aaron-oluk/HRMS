<?php

use App\Actions\Users\CreateUser;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;

test('a successful login writes an access-log row', function () {
    [, $user] = tenantWithRole('HR Admin');

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    expect(AuditLog::where('action', 'login_succeeded')->where('actor_id', $user->id)->exists())->toBeTrue();
});

test('a failed login writes an access-log row', function () {
    [, $user] = tenantWithRole('HR Admin');

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);

    expect(AuditLog::where('action', 'login_failed')->where('new_value', $user->email)->exists())->toBeTrue();
});

test('logging out writes an access-log row', function () {
    [, $user] = tenantWithRole('HR Admin');

    $this->actingAs($user)->post('/logout');

    expect(AuditLog::where('action', 'logged_out')->where('actor_id', $user->id)->exists())->toBeTrue();
});

test('viewing an employee sensitive fields writes a sensitive-field-viewed log entry', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    Employment::factory()->create(['tenant_id' => $tenant->id, 'employee_id' => $employee->id, 'entity_id' => $entity->id]);

    $this->actingAs($admin)->get(route('employees.show', $employee));

    expect(AuditLog::where('action', 'sensitive_field_viewed')
        ->where('auditable_id', $employee->id)
        ->where('actor_id', $admin->id)
        ->count())->toBe(3); // salary, identity-numbers, bank-details
});

test('a permission-denied route access writes an access-denied log entry', function () {
    [, $employeeUser] = tenantWithRole('Employee');

    $this->actingAs($employeeUser)->get(route('entities.index'))->assertForbidden();

    expect(AuditLog::where('action', 'access_denied')->where('actor_id', $employeeUser->id)->exists())->toBeTrue();
});

test('assigning a role to a new user writes a role-assigned log entry', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin);
    $newUser = app(CreateUser::class)->handle($tenant, [
        'name' => 'New Hire',
        'email' => 'new-hire@example.com',
        'password' => 'password',
        'status' => 'active',
        'role' => 'Employee',
    ]);

    expect(AuditLog::where('action', 'role_assigned')
        ->where('auditable_id', $newUser->id)
        ->where('actor_id', $admin->id)
        ->where('new_value', 'Employee')
        ->exists())->toBeTrue();
});
