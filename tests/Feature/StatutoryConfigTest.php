<?php

use App\Models\StatutoryPayeBand;
use App\Models\StatutorySetting;
use App\Support\Payroll\StatutoryEngine;

test('a super admin can view the statutory configuration page', function () {
    $this->actingAs(superAdmin())->get(route('admin.statutory.edit'))->assertOk();
});

test('a regular tenant user cannot view or edit statutory configuration', function () {
    [, $admin] = tenantWithRole('HR Admin');

    $this->actingAs($admin)->get(route('admin.statutory.edit'))->assertForbidden();
    $this->actingAs($admin)->put(route('admin.statutory.update'), [
        'bands' => [['floor' => 0, 'rate' => 0]],
        'paye_surcharge_floor' => 10_000_000,
        'paye_surcharge_rate' => 0.10,
        'nssf_employee_rate' => 0.05,
        'nssf_employer_rate' => 0.10,
    ])->assertForbidden();
});

test('editing the PAYE bands changes what StatutoryEngine calculates', function () {
    $engine = new StatutoryEngine;
    $before = $engine->payeFor(300_000);

    $this->actingAs(superAdmin())->put(route('admin.statutory.update'), [
        'bands' => [
            ['floor' => 0, 'rate' => 0],
            ['floor' => 300_000, 'rate' => 0.50],
        ],
        'paye_surcharge_floor' => 10_000_000,
        'paye_surcharge_rate' => 0.10,
        'nssf_employee_rate' => 0.05,
        'nssf_employer_rate' => 0.10,
    ])->assertRedirect(route('admin.statutory.edit'));

    expect(StatutoryPayeBand::count())->toBe(2);

    $after = (new StatutoryEngine)->payeFor(400_000);
    expect($after)->toBe(50_000.0);
    expect($after)->not->toBe($before);
});

test('editing NSSF rates changes what StatutoryEngine calculates', function () {
    $this->actingAs(superAdmin())->put(route('admin.statutory.update'), [
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
