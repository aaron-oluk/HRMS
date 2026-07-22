<?php

use App\Models\User;

test('a super admin can add another global platform admin', function () {
    $this->actingAs(superAdmin())->post(route('admin.super-admins.store'), [
        'name' => 'Second Admin',
        'email' => 'second-admin@aloflux.test',
        'password' => 'password123',
        'tier' => 'global',
    ])->assertRedirect(route('admin.super-admins.index'));

    $newAdmin = User::where('email', 'second-admin@aloflux.test')->firstOrFail();
    expect($newAdmin->is_super_admin)->toBeTrue();
    expect($newAdmin->is_org_admin)->toBeFalse();
    expect($newAdmin->tenant_id)->toBeNull();
});

test('a super admin can add a scoped org admin assigned to specific companies', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    [$tenantB] = tenantWithRole('HR Admin');

    $this->actingAs(superAdmin())->post(route('admin.super-admins.store'), [
        'name' => 'Org Admin',
        'email' => 'org-admin@aloflux.test',
        'password' => 'password123',
        'tier' => 'org',
        'tenant_ids' => [$tenantA->id],
    ])->assertRedirect(route('admin.super-admins.index'));

    $orgAdmin = User::where('email', 'org-admin@aloflux.test')->firstOrFail();
    expect($orgAdmin->is_super_admin)->toBeFalse();
    expect($orgAdmin->is_org_admin)->toBeTrue();
    expect($orgAdmin->assignedTenants->pluck('id')->all())->toBe([$tenantA->id]);
    expect($orgAdmin->canAccessTenant($tenantA))->toBeTrue();
    expect($orgAdmin->canAccessTenant($tenantB))->toBeFalse();
});

test('an org admin tier requires at least one assigned company', function () {
    $this->actingAs(superAdmin())->post(route('admin.super-admins.store'), [
        'name' => 'Org Admin', 'email' => 'org-admin@aloflux.test', 'password' => 'password123',
        'tier' => 'org',
    ])->assertSessionHasErrors('tenant_ids');
});

test('a super admin can view the platform admin list', function () {
    $this->actingAs(superAdmin())->get(route('admin.super-admins.index'))->assertOk();
});

test('a regular tenant user cannot manage platform admins', function () {
    [, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)->get(route('admin.super-admins.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.super-admins.create'))->assertForbidden();
    $this->actingAs($admin)->post(route('admin.super-admins.store'), [
        'name' => 'Nope', 'email' => 'nope@aloflux.test', 'password' => 'password123', 'tier' => 'global',
    ])->assertForbidden();
});

test('an org admin only sees their assigned companies in the console', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    [$tenantB] = tenantWithRole('HR Admin');

    $orgAdmin = User::factory()->create(['tenant_id' => null, 'is_org_admin' => true]);
    $orgAdmin->assignedTenants()->attach($tenantA->id);

    $response = $this->actingAs($orgAdmin)->get(route('admin.tenants.index'))->assertOk();
    $response->assertSee($tenantA->name);
    $response->assertDontSee($tenantB->name);
});

test('an org admin cannot access a company that is not assigned to them', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    [$tenantB] = tenantWithRole('HR Admin');

    $orgAdmin = User::factory()->create(['tenant_id' => null, 'is_org_admin' => true]);
    $orgAdmin->assignedTenants()->attach($tenantA->id);

    $this->actingAs($orgAdmin)->get(route('admin.tenants.show', $tenantA))->assertOk();
    $this->actingAs($orgAdmin)->get(route('admin.tenants.show', $tenantB))->assertForbidden();
});

test('an org admin cannot create new companies or manage other platform admins', function () {
    $orgAdmin = User::factory()->create(['tenant_id' => null, 'is_org_admin' => true]);

    $this->actingAs($orgAdmin)->get(route('admin.tenants.create'))->assertForbidden();
    $this->actingAs($orgAdmin)->post(route('admin.tenants.store'), [
        'name' => 'Nope', 'timezone' => 'Africa/Kampala', 'currency' => 'UGX',
        'admin_name' => 'Nope', 'admin_email' => 'nope@nope.test', 'admin_password' => 'password',
    ])->assertForbidden();
    $this->actingAs($orgAdmin)->get(route('admin.super-admins.index'))->assertForbidden();
});

test('a global platform admin is unaffected by org-admin scoping', function () {
    [$tenantA] = tenantWithRole('HR Admin');
    [$tenantB] = tenantWithRole('HR Admin');

    $global = superAdmin();

    $response = $this->actingAs($global)->get(route('admin.tenants.index'))->assertOk();
    $response->assertSee($tenantA->name);
    $response->assertSee($tenantB->name);

    $this->actingAs($global)->get(route('admin.tenants.show', $tenantA))->assertOk();
    $this->actingAs($global)->get(route('admin.tenants.show', $tenantB))->assertOk();
});

test('the root path sends both platform admin tiers to the console', function () {
    $orgAdmin = User::factory()->create(['tenant_id' => null, 'is_org_admin' => true]);

    $this->actingAs($orgAdmin)->get('/')->assertRedirect(route('admin.tenants.index'));
    $this->actingAs(superAdmin())->get('/')->assertRedirect(route('admin.tenants.index'));
});
