<?php

namespace App\Support\Payroll;

use App\Models\StatutoryPayeBand;
use App\Models\StatutorySetting;
use Illuminate\Support\Collection;

/**
 * Uganda statutory payroll calculations ("Country Pack v1" per DOC.md §3.9).
 *
 * DOC.md only supplies two anchor points for PAYE — a 235,000 UGX/month free threshold and a
 * 40% top marginal rate for high incomes — without the full band table. The bands (loaded from
 * the statutory_paye_bands/statutory_settings tables, editable at /admin/statutory) are Uganda's
 * standard published monthly PAYE schedule (Income Tax Act, employment income), and land on
 * exactly those two anchors: the first band is 0% up to 235,000, and the 30% top band plus a
 * 10% surcharge above 10,000,000/month works out to the 40% marginal DOC describes.
 *
 * Local Service Tax is out of scope: DOC.md describes it only as "configurable band table per
 * local authority" with no actual figures given anywhere, so it isn't computed here rather than
 * fabricating numbers and presenting them as authoritative.
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
