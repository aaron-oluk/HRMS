<?php

namespace App\Support\Payroll;

use App\Models\StatutoryPayeBand;
use App\Models\StatutorySetting;
use Illuminate\Support\Collection;

/**
 * Statutory payroll calculations, configured per tenant (see App\Models\StatutoryPayeBand /
 * App\Models\StatutorySetting, editable at /organization/statutory) since requirements vary by
 * organization — there is no shared platform-wide country pack. Both models use
 * App\Models\Concerns\BelongsToTenant, so every query below is automatically scoped to the
 * current tenant with no explicit tenant_id filtering needed here.
 *
 * New tenants start from Uganda's standard published monthly PAYE schedule (Income Tax Act,
 * employment income) via App\Actions\Tenancy\SeedDefaultStatutoryConfig, matching the two
 * anchor points DOC.md §3.9 gives (235,000 UGX/month free threshold, 40% top marginal rate —
 * the latter being the 30% top band plus a 10% surcharge above 10,000,000/month) — but any
 * tenant may edit its own copy from there.
 *
 * This is constructor-injected once per GeneratePayrollRun::handle() call and reused across
 * every employee in that run's loop, so the bands/settings are loaded from the database once
 * (on first access) and memoized here for the rest of the instance's lifetime.
 */
class StatutoryEngine
{
    /** @var Collection<int, StatutoryPayeBand>|null */
    private ?Collection $bands = null;

    private ?StatutorySetting $settings = null;

    public function payeFor(float|string $basicSalary): float
    {
        $salary = (float) $basicSalary;
        $bands = $this->bands();
        $tax = 0.0;

        foreach ($bands as $index => $band) {
            $floor = (float) $band->floor;
            $nextFloor = isset($bands[$index + 1]) ? (float) $bands[$index + 1]->floor : null;

            if ($salary <= $floor) {
                break;
            }

            $sliceTop = $nextFloor !== null ? min($salary, $nextFloor) : $salary;
            $tax += ($sliceTop - $floor) * (float) $band->rate;
        }

        $settings = $this->settings();

        if ($salary > (float) $settings->paye_surcharge_floor) {
            $tax += ($salary - (float) $settings->paye_surcharge_floor) * (float) $settings->paye_surcharge_rate;
        }

        return round($tax, 2);
    }

    public function nssfEmployeeFor(float|string $basicSalary): float
    {
        return round((float) $basicSalary * (float) $this->settings()->nssf_employee_rate, 2);
    }

    public function nssfEmployerFor(float|string $basicSalary): float
    {
        return round((float) $basicSalary * (float) $this->settings()->nssf_employer_rate, 2);
    }

    /**
     * @return Collection<int, StatutoryPayeBand>
     */
    private function bands(): Collection
    {
        return $this->bands ??= StatutoryPayeBand::query()->orderBy('order')->get()->values();
    }

    private function settings(): StatutorySetting
    {
        return $this->settings ??= StatutorySetting::current();
    }
}
