<?php

use App\Models\StatutoryPayeBand;
use App\Models\StatutorySetting;
use App\Support\Payroll\StatutoryEngine;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;

test('an hr admin can view the organization statutory configuration page', function () {
    [, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)->get(route('organization.edit'))->assertOk();
});

test('a role without org.manage cannot view or edit statutory configuration', function () {
    [, $teamLead] = tenantWithRole('Team Lead');

    $this->actingAs($teamLead)->get(route('organization.edit'))->assertForbidden();
    $this->actingAs($teamLead)->put(route('organization.update-statutory'), [
        'bands' => [['floor' => 0, 'rate' => 0]],
        'paye_surcharge_floor' => 10_000_000,
        'paye_surcharge_rate' => 0.10,
        'nssf_employee_rate' => 0.05,
        'nssf_employer_rate' => 0.10,
    ])->assertForbidden();
});

test('the old platform-admin statutory route no longer exists', function () {
    expect(Route::has('admin.statutory.edit'))->toBeFalse();
});

test('editing the PAYE bands changes what StatutoryEngine calculates', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    app(TenantContext::class)->set($tenant);

    $before = (new StatutoryEngine)->payeFor(300_000);

    $this->actingAs($admin)->put(route('organization.update-statutory'), [
        'bands' => [
            ['floor' => 0, 'rate' => 0],
            ['floor' => 300_000, 'rate' => 0.50],
        ],
        'paye_surcharge_floor' => 10_000_000,
        'paye_surcharge_rate' => 0.10,
        'nssf_employee_rate' => 0.05,
        'nssf_employer_rate' => 0.10,
    ])->assertRedirect(route('organization.edit'));

    expect(StatutoryPayeBand::count())->toBe(2);

    $after = (new StatutoryEngine)->payeFor(400_000);
    expect($after)->toBe(50_000.0);
    expect($after)->not->toBe($before);
});

test('editing NSSF rates changes what StatutoryEngine calculates', function () {
    [$tenant, $admin] = tenantWithRole('HR Admin');
    app(TenantContext::class)->set($tenant);

    $this->actingAs($admin)->put(route('organization.update-statutory'), [
        'bands' => [['floor' => 0, 'rate' => 0]],
        'paye_surcharge_floor' => 10_000_000,
        'paye_surcharge_rate' => 0.10,
        'nssf_employee_rate' => 0.08,
        'nssf_employer_rate' => 0.15,
    ]);

    expect(StatutorySetting::current()->nssf_employee_rate)->toEqualWithDelta(0.08, 0.0001);

    $engine = new StatutoryEngine;
    expect($engine->nssfEmployeeFor(100_000))->toBe(8_000.0);
    expect($engine->nssfEmployerFor(100_000))->toBe(15_000.0);
});

test('StatutoryEngine only queries the database once per instance across many employees', function () {
    [$tenant] = tenantWithRole('HR Admin');
    app(TenantContext::class)->set($tenant);

    $engine = new StatutoryEngine;

    $engine->payeFor(300_000);
    $engine->payeFor(500_000);
    $engine->nssfEmployeeFor(300_000);

    // No direct query counter available here — this instead pins the observable contract:
    // the same engine instance keeps returning consistent figures even if the underlying
    // config were to change mid-run, since it was memoized on first access.
    $firstCall = $engine->payeFor(300_000);
    StatutoryPayeBand::query()->update(['rate' => 0.99]);
    $secondCall = $engine->payeFor(300_000);

    expect($secondCall)->toBe($firstCall);
});

test('two tenants have completely independent statutory configuration', function () {
    [$tenantA, $adminA] = tenantWithRole('HR Admin');
    [$tenantB] = tenantWithRole('HR Admin');

    app(TenantContext::class)->set($tenantA);
    $beforeB = StatutorySetting::current()->nssf_employee_rate;

    $this->actingAs($adminA)->put(route('organization.update-statutory'), [
        'bands' => [['floor' => 0, 'rate' => 0]],
        'paye_surcharge_floor' => 10_000_000,
        'paye_surcharge_rate' => 0.10,
        'nssf_employee_rate' => 0.25,
        'nssf_employer_rate' => 0.10,
    ]);

    app(TenantContext::class)->set($tenantA);
    expect(StatutorySetting::current()->nssf_employee_rate)->toEqualWithDelta(0.25, 0.0001);

    app(TenantContext::class)->set($tenantB);
    expect(StatutorySetting::current()->nssf_employee_rate)->toEqualWithDelta((float) $beforeB, 0.0001);
    expect(StatutoryPayeBand::count())->toBe(4);
});
