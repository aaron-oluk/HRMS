<?php

use App\Models\Theme;

test('a global admin can create a theme', function () {
    $this->actingAs(superAdmin())->post(route('admin.themes.store'), [
        'name' => 'Forest', 'slug' => 'forest',
        'color_50' => '#f0fdf4', 'color_100' => '#dcfce7', 'color_500' => '#22c55e',
        'color_600' => '#16a34a', 'color_700' => '#15803d', 'color_800' => '#166534',
        'font_stack' => 'ui-sans-serif, system-ui, sans-serif',
    ])->assertRedirect(route('admin.themes.index'));

    expect(Theme::where('slug', 'forest')->exists())->toBeTrue();
});

test('a global admin can edit a theme', function () {
    $theme = Theme::factory()->create(['name' => 'Old Name']);

    $this->actingAs(superAdmin())->put(route('admin.themes.update', $theme), [
        'name' => 'New Name', 'slug' => $theme->slug,
        'color_50' => '#ffffff', 'color_100' => '#eeeeee', 'color_500' => '#888888',
        'color_600' => '#666666', 'color_700' => '#444444', 'color_800' => '#222222',
        'font_stack' => 'ui-monospace, monospace',
    ])->assertRedirect(route('admin.themes.index'));

    expect($theme->refresh()->name)->toBe('New Name');
});

test('the default theme cannot be deleted', function () {
    $default = Theme::where('is_default', true)->firstOrFail();

    $this->actingAs(superAdmin())->delete(route('admin.themes.destroy', $default))
        ->assertSessionHasErrors('theme');

    expect(Theme::find($default->id))->not->toBeNull();
});

test('a non-global-admin cannot manage themes', function () {
    [, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)->get(route('admin.themes.index'))->assertForbidden();
});

test('an hr admin can pick a theme for their organization', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    $theme = Theme::factory()->create(['name' => 'Ocean-like']);

    $this->actingAs($admin)->put(route('organization.update-theme'), [
        'theme_id' => $theme->id,
    ])->assertRedirect(route('organization.edit'));

    expect($tenant->refresh()->theme_id)->toBe($theme->id);
});

test('a tenant with no theme selected falls back to the default theme', function () {
    [$tenant] = tenantWithRole('HR Admin');

    expect($tenant->theme_id)->toBeNull();
    expect($tenant->activeTheme()->is_default)->toBeTrue();
});

test('the default theme is not injected as an inline style override, but a picked theme is', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');

    $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();
    $response->assertDontSee('--theme-600:', false);

    $theme = Theme::factory()->create(['color_600' => '#123456']);
    $tenant->update(['theme_id' => $theme->id]);

    // Re-fetch: the previous request already cached its tenant relation on this in-memory
    // $admin instance, and actingAs() reuses the same object.
    $response = $this->actingAs($admin->fresh())->get(route('dashboard'))->assertOk();
    $response->assertSee('--theme-600: #123456', false);
});
