<?php

namespace App\Actions\Payroll;

use App\Models\EmployeeAdvance;
use App\Models\EmployeeDeduction;
use App\Models\Employment;
use App\Models\Entity;
use App\Models\PayrollRun;
use App\Models\PayrollRunLine;
use App\Models\User;
use App\Support\Payroll\StatutoryEngine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GeneratePayrollRun
{
    public function __construct(protected StatutoryEngine $statutoryEngine) {}

    public function handle(Entity $entity, string $periodMonth, User $actor): PayrollRun
    {
        $period = Carbon::parse($periodMonth)->startOfMonth();

        if (PayrollRun::where('entity_id', $entity->id)->whereDate('period_month', $period)->exists()) {
            throw ValidationException::withMessages([
                'period_month' => 'A payroll run already exists for this entity and period.',
            ]);
        }

        return DB::transaction(function () use ($entity, $period, $actor) {
            $run = PayrollRun::create([
                'entity_id' => $entity->id,
                'period_month' => $period->toDateString(),
                'status' => 'draft',
                'generated_by' => $actor->id,
            ]);

            Employment::where('entity_id', $entity->id)
                ->whereNull('effective_to')
                ->where('status', 'active')
                ->with('employee')
                ->get()
                ->each(function (Employment $employment) use ($run, $period) {
                    $basicSalary = (float) $employment->basic_salary;
                    $paye = $this->statutoryEngine->payeFor($basicSalary);
                    $nssfEmployee = $this->statutoryEngine->nssfEmployeeFor($basicSalary);

                    $line = $run->lines()->create([
                        'employee_id' => $employment->employee_id,
                        'employment_id' => $employment->id,
                        'basic_salary' => $basicSalary,
                        'gross_pay' => $basicSalary,
                        'paye_amount' => $paye,
                        'nssf_employee_amount' => $nssfEmployee,
                        'nssf_employer_amount' => $this->statutoryEngine->nssfEmployerFor($basicSalary),
                        'other_deductions' => 0,
                        'net_pay' => $basicSalary - $paye - $nssfEmployee,
                        'currency' => $employment->currency,
                    ]);

                    $otherDeductions = $this->applyAdvancesAndDeductions($line, $employment->employee_id, $period);

                    $line->update([
                        'other_deductions' => $otherDeductions,
                        'net_pay' => $basicSalary - $paye - $nssfEmployee - $otherDeductions,
                    ]);
                });

            return $run;
        });
    }

    /**
     * Applies this employee's active advances and deductions to the given payroll run line,
     * recording a PayrollRunLineDeduction per applied item so payslips can show why net pay
     * was reduced, and returns the total to subtract from net pay.
     */
    private function applyAdvancesAndDeductions(PayrollRunLine $line, int $employeeId, Carbon $period): float
    {
        $total = 0.0;

        EmployeeAdvance::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('balance_remaining', '>', 0)
            ->get()
            ->each(function (EmployeeAdvance $advance) use ($line, &$total): void {
                $amount = min((float) $advance->monthly_deduction, (float) $advance->balance_remaining);
                $remaining = (float) $advance->balance_remaining - $amount;

                $line->deductions()->create([
                    'source_type' => EmployeeAdvance::class,
                    'source_id' => $advance->id,
                    'label' => 'Advance repayment'.($advance->reason ? " ({$advance->reason})" : ''),
                    'amount' => $amount,
                ]);

                $advance->update([
                    'balance_remaining' => $remaining,
                    'status' => $remaining <= 0 ? 'settled' : 'active',
                ]);

                $total += $amount;
            });

        EmployeeDeduction::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('effective_date', '<=', $period)
            ->get()
            ->each(function (EmployeeDeduction $deduction) use ($line, &$total): void {
                $amount = (float) $deduction->amount;

                $line->deductions()->create([
                    'source_type' => EmployeeDeduction::class,
                    'source_id' => $deduction->id,
                    'label' => $deduction->label,
                    'amount' => $amount,
                ]);

                if ($deduction->frequency === 'one_time') {
                    $deduction->update(['status' => 'inactive']);
                }

                $total += $amount;
            });

        return $total;
    }
}
