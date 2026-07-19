<?php

namespace App\Support\Payroll;

/**
 * Uganda statutory payroll calculations ("Country Pack v1" per DOC.md §3.9).
 *
 * DOC.md only supplies two anchor points for PAYE — a 235,000 UGX/month free threshold and a
 * 40% top marginal rate for high incomes — without the full band table. The bands below are
 * Uganda's standard published monthly PAYE schedule (Income Tax Act, employment income), and
 * land on exactly those two anchors: the first band is 0% up to 235,000, and the 30% top band
 * plus a 10% surcharge above 10,000,000/month works out to the 40% marginal DOC describes.
 *
 * Local Service Tax is out of scope: DOC.md describes it only as "configurable band table per
 * local authority" with no actual figures given anywhere, so it isn't computed here rather than
 * fabricating numbers and presenting them as authoritative.
 */
class StatutoryEngine
{
    /**
     * Each band taxes only the slice of income between the previous floor and its own floor,
     * at its own rate — cumulative tax is the running sum, never recomputed from scratch.
     */
    private const PAYE_BANDS = [
        ['floor' => 0, 'rate' => 0.0],
        ['floor' => 235_000, 'rate' => 0.10],
        ['floor' => 335_000, 'rate' => 0.20],
        ['floor' => 410_000, 'rate' => 0.30],
    ];

    private const PAYE_SURCHARGE_FLOOR = 10_000_000;

    private const PAYE_SURCHARGE_RATE = 0.10;

    private const NSSF_EMPLOYEE_RATE = 0.05;

    private const NSSF_EMPLOYER_RATE = 0.10;

    public function payeFor(float|string $basicSalary): float
    {
        $salary = (float) $basicSalary;
        $tax = 0.0;

        foreach (self::PAYE_BANDS as $index => $band) {
            $nextFloor = self::PAYE_BANDS[$index + 1]['floor'] ?? null;

            if ($salary <= $band['floor']) {
                break;
            }

            $sliceTop = $nextFloor !== null ? min($salary, $nextFloor) : $salary;
            $tax += ($sliceTop - $band['floor']) * $band['rate'];
        }

        if ($salary > self::PAYE_SURCHARGE_FLOOR) {
            $tax += ($salary - self::PAYE_SURCHARGE_FLOOR) * self::PAYE_SURCHARGE_RATE;
        }

        return round($tax, 2);
    }

    public function nssfEmployeeFor(float|string $basicSalary): float
    {
        return round((float) $basicSalary * self::NSSF_EMPLOYEE_RATE, 2);
    }

    public function nssfEmployerFor(float|string $basicSalary): float
    {
        return round((float) $basicSalary * self::NSSF_EMPLOYER_RATE, 2);
    }
}
