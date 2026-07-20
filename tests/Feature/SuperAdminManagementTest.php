<?php

use App\Models\User;

test('a super admin can add another platform admin', function () {
    $this->actingAs(superAdmin())->post(route('admin.super-admins.store'), [
        'name' => 'Second Admin',
        'email' => 'second-admin@aloflux.test',
        'password' => 'password123',
    ])->assertRedirect(route('admin.super-admins.index'));

    $newAdmin = User::where('email', 'second-admin@aloflux.test')->firstOrFail();
    expect($newAdmin->is_super_admin)->toBeTrue();
    expect($newAdmin->tenant_id)->toBeNull();
});

test('a super admin can view the platform admin list', function () {
    $this->actingAs(superAdmin())->get(route('admin.super-admins.index'))->assertOk();
});

test('a regular tenant user cannot manage platform admins', function () {
    [, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)->get(route('admin.super-admins.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('admin.super-admins.create'))->assertForbidden();
    $this->actingAs($admin)->post(route('admin.super-admins.store'), [
        'name' => 'Nope', 'email' => 'nope@aloflux.test', 'password' => 'password123',
    ])->assertForbidden();
});
