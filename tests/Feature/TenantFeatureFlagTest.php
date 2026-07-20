<?php

use App\Models\Tenant;
use App\Models\TenantFeatureFlag;

test('a module with no flag row is enabled by default', function () {
    $tenant = Tenant::factory()->create();

    expect($tenant->hasModule('payroll'))->toBeTrue();
});

test('disabling a module blocks its routes and enabling it restores access', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)->get(route('reports.index'))->assertOk();

    TenantFeatureFlag::create(['tenant_id' => $tenant->id, 'module' => 'reports', 'enabled' => false]);

    // Re-fetch the user: the previous request already cached its tenant/featureFlags
    // relations on this in-memory $admin instance, and actingAs() reuses the same object.
    $this->actingAs($admin->fresh())->get(route('reports.index'))->assertForbidden();

    TenantFeatureFlag::where('tenant_id', $tenant->id)->where('module', 'reports')->update(['enabled' => true]);

    $this->actingAs($admin->fresh())->get(route('reports.index'))->assertOk();
});

test('disabling a module hides its nav link', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)->get(route('dashboard'))->assertSee('Reports');

    TenantFeatureFlag::create(['tenant_id' => $tenant->id, 'module' => 'reports', 'enabled' => false]);

    $this->actingAs($admin->fresh())->get(route('dashboard'))->assertDontSee('Reports');
});

test('a super admin can toggle a company\'s enabled modules', function () {
    [$tenant] = tenantWithRole('HR Admin');

    $this->actingAs(superAdmin())->put(route('admin.tenants.modules.update', $tenant), [
        'modules' => ['payroll', 'reports'],
    ])->assertRedirect(route('admin.tenants.show', $tenant));

    $tenant->refresh();
    expect($tenant->hasModule('payroll'))->toBeTrue();
    expect($tenant->hasModule('reports'))->toBeTrue();
    expect($tenant->hasModule('engagement'))->toBeFalse();
    expect($tenant->hasModule('cases'))->toBeFalse();
});

test('a regular tenant user cannot toggle enabled modules', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)->put(route('admin.tenants.modules.update', $tenant), [
        'modules' => ['payroll'],
    ])->assertForbidden();
});
