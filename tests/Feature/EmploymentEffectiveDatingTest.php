<?php

use App\Actions\Employments\RecordEmploymentChange;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Entity;
use App\Models\Position;

test('recording a new employment closes the previous active one without deleting it', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $original = Employment::factory()->create([
        'tenant_id' => $tenant->id,
        'employee_id' => $employee->id,
        'entity_id' => $entity->id,
        'basic_salary' => 2000000,
        'effective_from' => now()->subYear()->toDateString(),
        'effective_to' => null,
        'status' => 'active',
    ]);

    $newDepartment = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $newPosition = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $promoted = app(RecordEmploymentChange::class)->handle($employee, [
        'entity_id' => $entity->id,
        'department_id' => $newDepartment->id,
        'position_id' => $newPosition->id,
        'employment_type' => 'full_time',
        'basic_salary' => 3000000,
        'currency' => 'UGX',
        'effective_from' => now()->toDateString(),
        'reason' => 'promotion',
    ]);

    $original->refresh();

    expect($original->status)->toBe('superseded');
    expect($original->effective_to)->not->toBeNull();
    expect((float) $original->basic_salary)->toBe(2000000.0);

    expect($promoted->status)->toBe('active');
    expect($promoted->effective_to)->toBeNull();
    expect((float) $promoted->basic_salary)->toBe(3000000.0);

    expect(Employment::where('employee_id', $employee->id)->count())->toBe(2);
});

test('a first employment record does not need a previous one to close', function () {
    [$tenant] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $department = Department::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $position = Position::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $employment = app(RecordEmploymentChange::class)->handle($employee, [
        'entity_id' => $entity->id,
        'department_id' => $department->id,
        'position_id' => $position->id,
        'employment_type' => 'full_time',
        'basic_salary' => 1500000,
        'currency' => 'UGX',
        'effective_from' => now()->toDateString(),
        'reason' => 'initial',
    ]);

    expect($employment->status)->toBe('active');
    expect(Employment::where('employee_id', $employee->id)->count())->toBe(1);
});
