<?php

use App\Models\Tenant;

test('a super admin can log in as a company\'s HR Admin', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $superAdmin = superAdmin();

    $this->actingAs($superAdmin)->post(route('admin.tenants.impersonate', $tenant))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($admin);
    expect(session('impersonator_id'))->toBe($superAdmin->id);

    // Now authenticated as the tenant's real HR Admin — the dashboard resolves under
    // that tenant's own context and permissions, not the bypassed super-admin ones.
    $this->get(route('dashboard'))->assertOk();
});

test('impersonation is refused for a company with no HR Admin', function () {
    $tenant = Tenant::factory()->create();
    $superAdmin = superAdmin();

    $this->actingAs($superAdmin)->post(route('admin.tenants.impersonate', $tenant))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->assertAuthenticatedAs($superAdmin);
});

test('stopping impersonation returns to the original super admin session', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $superAdmin = superAdmin();

    $this->actingAs($superAdmin)->post(route('admin.tenants.impersonate', $tenant));
    $this->assertAuthenticatedAs($admin);

    $this->post(route('impersonation.stop'))->assertRedirect(route('admin.tenants.index'));

    $this->assertAuthenticatedAs($superAdmin);
    expect(session('impersonator_id'))->toBeNull();
});

test('stopping impersonation without an active impersonation session 404s', function () {
    [, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)->post(route('impersonation.stop'))->assertNotFound();
});
