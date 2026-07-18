<?php

use App\Models\Employee;
use App\Models\Entity;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Laravel\Sanctum\Sanctum;

test('a super admin bypasses every permission check regardless of role', function () {
    $superAdmin = User::factory()->create(['tenant_id' => null, 'is_super_admin' => true]);

    expect($superAdmin->can('anything.at.all'))->toBeTrue();
    expect($superAdmin->can('users.manage'))->toBeTrue();
});

test('a super admin sees employees across tenants once no tenant context is set', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    $entityA = Entity::factory()->create(['tenant_id' => $tenantA->id]);
    $employeeA = Employee::factory()->for($entityA)->create(['tenant_id' => $tenantA->id]);

    [$tenantB] = tenantWithRole('HR Admin');
    $entityB = Entity::factory()->create(['tenant_id' => $tenantB->id]);
    $employeeB = Employee::factory()->for($entityB)->create(['tenant_id' => $tenantB->id]);

    $superAdmin = User::factory()->create(['tenant_id' => null, 'is_super_admin' => true]);

    // A fresh production request for a tenant-less super admin never resolves a tenant
    // context (IdentifyTenant only sets one when $user->tenant is present); reset the
    // singleton here to undo the leftover context from the fixture setup above.
    app(TenantContext::class)->set(null);

    Sanctum::actingAs($superAdmin);

    $response = $this->getJson('/api/v1/employees')->assertOk();

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($employeeA->id);
    expect($ids)->toContain($employeeB->id);
});
