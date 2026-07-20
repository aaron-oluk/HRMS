<?php

use App\Models\Employee;
use App\Models\EmployeeCompensationItem;
use App\Models\Entity;

test('an hr admin can add a compensation item to an employee', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->post(route('employees.compensation-items.store', $employee), [
        'category' => 'allowance',
        'name' => 'Transport',
        'amount' => 120000,
    ])->assertRedirect(route('employees.show', $employee));

    $item = EmployeeCompensationItem::where('employee_id', $employee->id)->firstOrFail();
    expect($item->category)->toBe('allowance');
    expect($item->name)->toBe('Transport');
    expect((float) $item->amount)->toBe(120000.0);
    expect($item->tenant_id)->toBe($tenant->id);
});

test('an hr admin can remove a compensation item', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);
    $item = EmployeeCompensationItem::factory()->for($employee)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->delete(route('employees.compensation-items.destroy', [$employee, $item]))
        ->assertRedirect(route('employees.show', $employee));

    expect(EmployeeCompensationItem::find($item->id))->toBeNull();
});

test('an hr specialist cannot manage compensation items', function () {
    [$tenant, $specialist] = tenantWithRole('HR Specialist');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($specialist)->post(route('employees.compensation-items.store', $employee), [
        'category' => 'allowance',
        'name' => 'Transport',
        'amount' => 100,
    ])->assertForbidden();
});

test('compensation items require a valid category', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $entity = Entity::factory()->create(['tenant_id' => $tenant->id]);
    $employee = Employee::factory()->for($entity)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($admin)->post(route('employees.compensation-items.store', $employee), [
        'category' => 'not-a-real-category',
        'name' => 'Transport',
        'amount' => 100,
    ])->assertSessionHasErrors('category');
});
