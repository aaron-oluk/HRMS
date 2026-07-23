<?php

use App\Models\ReportFavorite;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('a report viewer can favorite and unfavorite a report', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdmin)->post(route('reports.favorites.store'), [
        'report_key' => 'headcount',
    ])->assertRedirect();

    $favorite = ReportFavorite::where('user_id', $hrAdmin->id)->where('report_key', 'headcount')->firstOrFail();
    expect($favorite->tenant_id)->toBe($tenant->id);

    $this->actingAs($hrAdmin)->delete(route('reports.favorites.destroy', 'headcount'))
        ->assertRedirect();

    expect(ReportFavorite::find($favorite->id))->toBeNull();
});

test('favoriting the same report twice does not duplicate', function () {
    [, $hrAdmin] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdmin)->post(route('reports.favorites.store'), ['report_key' => 'payroll']);
    $this->actingAs($hrAdmin)->post(route('reports.favorites.store'), ['report_key' => 'payroll']);

    expect(ReportFavorite::where('user_id', $hrAdmin->id)->where('report_key', 'payroll')->count())->toBe(1);
});

test('favoriting requires a valid report key', function () {
    [, $hrAdmin] = tenantWithRole('HR Admin');

    $this->actingAs($hrAdmin)->post(route('reports.favorites.store'), [
        'report_key' => 'not-a-real-report',
    ])->assertSessionHasErrors('report_key');
});

test('a role without reports.view cannot favorite a report', function () {
    [, $employeeUser] = tenantWithRole('Employee');

    $this->actingAs($employeeUser)->post(route('reports.favorites.store'), [
        'report_key' => 'headcount',
    ])->assertForbidden();
});

test('unfavoriting only removes the acting user\'s own favorite', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');

    app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    $hrManagerRole = Role::where('tenant_id', $tenant->id)->where('name', 'HR Manager')->firstOrFail();
    $hrManager = User::factory()->create(['tenant_id' => $tenant->id]);
    $hrManager->assignRole($hrManagerRole);

    $mine = ReportFavorite::factory()->create(['user_id' => $hrAdmin->id, 'tenant_id' => $tenant->id, 'report_key' => 'headcount']);
    $theirs = ReportFavorite::factory()->create(['user_id' => $hrManager->id, 'tenant_id' => $tenant->id, 'report_key' => 'headcount']);

    $this->actingAs($hrAdmin)->delete(route('reports.favorites.destroy', 'headcount'));

    expect(ReportFavorite::find($mine->id))->toBeNull();
    expect(ReportFavorite::find($theirs->id))->not->toBeNull();
});

test('the reports index shows favorited reports in a separate section', function () {
    [$tenant, $hrAdmin] = tenantWithRole('HR Admin');
    ReportFavorite::factory()->create(['user_id' => $hrAdmin->id, 'tenant_id' => $tenant->id, 'report_key' => 'payroll']);

    $response = $this->actingAs($hrAdmin)->get(route('reports.index'));

    $response->assertOk();
    $response->assertSeeInOrder(['Your favorites', 'Payroll cost summary', 'All reports']);
});
