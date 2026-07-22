<?php

namespace App\Actions\Tenancy;

use App\Models\StatutoryPayeBand;
use App\Models\StatutorySetting;
use App\Models\Tenant;

/**
 * Gives a newly onboarded tenant a working starting point for statutory config — Uganda's
 * standard published monthly PAYE schedule (see App\Support\Payroll\StatutoryEngine's
 * docblock for where these figures come from). Every tenant owns its own copy from here on
 * and can edit it independently (see App\Http\Controllers\Web\StatutoryConfigController) —
 * this is deliberately not a shared platform-wide default, since requirements vary per
 * organization.
 */
class SeedDefaultStatutoryConfig
{
    public function handle(Tenant $tenant): void
    {
        foreach ([
            ['floor' => 0, 'rate' => 0, 'order' => 1],
            ['floor' => 235_000, 'rate' => 0.10, 'order' => 2],
            ['floor' => 335_000, 'rate' => 0.20, 'order' => 3],
            ['floor' => 410_000, 'rate' => 0.30, 'order' => 4],
        ] as $band) {
            StatutoryPayeBand::create(['tenant_id' => $tenant->id, ...$band]);
        }

        StatutorySetting::create([
            'tenant_id' => $tenant->id,
            'paye_surcharge_floor' => 10_000_000,
            'paye_surcharge_rate' => 0.10,
            'nssf_employee_rate' => 0.05,
            'nssf_employer_rate' => 0.10,
        ]);
    }
}
