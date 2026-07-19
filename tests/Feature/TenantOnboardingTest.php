<?php

use App\Actions\Tenancy\ProvisionDefaultRoles;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function superAdmin(): User
{
    $superAdmin = User::factory()->create(['tenant_id' => null, 'is_super_admin' => true]);

    // A fresh production request for a tenant-less super admin never resolves a tenant
    // context; reset the singleton here to undo any leftover context from other fixtures.
    app(TenantContext::class)->set(null);

    return $superAdmin;
}

test('a super admin can view the platform admin console', function () {
    $this->actingAs(superAdmin())->get(route('admin.tenants.index'))->assertOk();
});

test('a regular tenant user cannot access the platform admin console', function () {
    [, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)->get(route('admin.tenants.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.tenants.create'))->assertForbidden();
});

test('a guest is redirected to login', function () {
    $this->get(route('admin.tenants.index'))->assertRedirect(route('login'));
});

test('a super admin can onboard a new company end to end', function () {
    $this->actingAs(superAdmin())->post(route('admin.tenants.store'), [
        'name' => 'Kampala Widgets Ltd',
        'timezone' => 'Africa/Kampala',
        'currency' => 'UGX',
        'admin_name' => 'Jane Founder',
        'admin_email' => 'jane@kampalawidgets.test',
        'admin_password' => 'password123',
    ])->assertRedirect(route('admin.tenants.index'));

    $tenant = Tenant::where('name', 'Kampala Widgets Ltd')->firstOrFail();
    expect($tenant->slug)->toBe('kampala-widgets-ltd');
    expect($tenant->currency)->toBe('UGX');

    $roleCount = Role::where('tenant_id', $tenant->id)->count();
    expect($roleCount)->toBe(count(ProvisionDefaultRoles::ROLE_PERMISSIONS));

    $newAdmin = User::where('email', 'jane@kampalawidgets.test')->firstOrFail();
    expect($newAdmin->tenant_id)->toBe($tenant->id);

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    expect($newAdmin->hasRole('HR Admin'))->toBeTrue();

    // The new admin can log in immediately. Log the super admin out first, since
    // Fortify's login route sits behind guest-only middleware that would otherwise
    // silently no-op this request while the super admin's session is still active.
    $this->post(route('logout'));
    $this->post('/login', ['email' => 'jane@kampalawidgets.test', 'password' => 'password123']);
    $this->assertAuthenticatedAs($newAdmin);
});

test('the root path sends a super admin to the console and everyone else to the dashboard', function () {
    $this->actingAs(superAdmin())->get('/')->assertRedirect(route('admin.tenants.index'));

    [, $admin] = tenantWithRole('HR Admin');
    $this->actingAs($admin)->get('/')->assertRedirect(route('dashboard'));
});
