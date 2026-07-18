<?php

use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;
use Laravel\Sanctum\Sanctum;

test('unauthenticated requests are rejected', function () {
    $this->getJson('/api/v1/employees')->assertUnauthorized();
});

test('an authenticated tenant user can list their employees', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    Sanctum::actingAs($admin);

    $this->getJson('/api/v1/employees')
        ->assertOk()
        ->assertJsonFragment(['employee_number' => $employee->employee_number]);
});

test('a manager cannot see sensitive fields through the api', function () {
    [$tenant, $manager] = tenantWithRole('Team Lead');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create([
        'tenant_id' => $tenant->id,
        'national_id_number' => 'CM12345678900',
    ]);
    Employment::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'entity_id' => $entity->id,
        'basic_salary' => 2750000,
    ]);

    Sanctum::actingAs($manager);

    $response = $this->getJson("/api/v1/employees/{$employee->id}")->assertOk();

    $response->assertJsonMissing(['national_id_number' => 'CM12345678900']);
    expect($response->json('data.national_id_number'))->toBeNull();
});

test('an hr admin can create an employee through the api', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/v1/employees', [
        'entity_id' => $entity->id,
        'employee_number' => 'EMP-API-001',
        'first_name' => 'Amara',
        'last_name' => 'Kintu',
        'status' => 'active',
    ]);

    $response->assertCreated();
    expect(Employee::where('employee_number', 'EMP-API-001')->where('tenant_id', $tenant->id)->exists())->toBeTrue();
});

test('a tenant cannot fetch another tenant\'s employee through the api', function () {
    [$tenantA, $adminA] = tenantWithRole('HR Admin');

    [$tenantB] = tenantWithRole('HR Admin');
    $entityB = Entity::factory()->create(['tenant_id' => $tenantB->id]);
    $employeeB = Employee::factory()->for($entityB)->create(['tenant_id' => $tenantB->id]);

    Sanctum::actingAs($adminA);

    $this->getJson("/api/v1/employees/{$employeeB->id}")->assertNotFound();
});
