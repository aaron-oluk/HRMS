<?php

use App\Models\Tenant;

test('a super admin can edit a company', function () {
    $tenant = Tenant::factory()->create(['name' => 'Old Name']);

    $this->actingAs(superAdmin())->put(route('admin.tenants.update', $tenant), [
        'name' => 'New Name',
        'timezone' => 'Africa/Nairobi',
        'currency' => 'KES',
    ])->assertRedirect(route('admin.tenants.index'));

    expect($tenant->refresh())
        ->name->toBe('New Name')
        ->timezone->toBe('Africa/Nairobi')
        ->currency->toBe('KES');
});

test('suspending a company blocks its users from making any request', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');

    $this->actingAs(superAdmin())->post(route('admin.tenants.suspend', $tenant))
        ->assertRedirect(route('admin.tenants.index'));

    expect($tenant->refresh()->status)->toBe('suspended');

    $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('reactivating a company restores access', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $tenant->update(['status' => 'suspended']);

    $this->actingAs(superAdmin())->post(route('admin.tenants.reactivate', $tenant))
        ->assertRedirect(route('admin.tenants.index'));

    expect($tenant->refresh()->status)->toBe('active');

    $this->actingAs($admin)->get(route('dashboard'))->assertOk();
});

test('a regular tenant user cannot edit, suspend, or reactivate a company', function () {
    [, $admin] = tenantWithRole('HR Admin');
    $tenant = Tenant::factory()->create();

    $this->actingAs($admin)->put(route('admin.tenants.update', $tenant), [
        'name' => 'Nope', 'timezone' => 'Africa/Kampala', 'currency' => 'UGX',
    ])->assertForbidden();

    $this->actingAs($admin)->post(route('admin.tenants.suspend', $tenant))->assertForbidden();
    $this->actingAs($admin)->post(route('admin.tenants.reactivate', $tenant))->assertForbidden();
});

test('a super admin can view a company health page', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');

    $response = $this->actingAs(superAdmin())->get(route('admin.tenants.show', $tenant))->assertOk();

    $response->assertSee($tenant->name);
    $response->assertSee($admin->name);
});
