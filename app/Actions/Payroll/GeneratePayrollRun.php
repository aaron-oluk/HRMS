<?php

namespace App\Actions\Payroll;

use App\Models\Employment;
use App\Models\Entity;
use App\Models\PayrollRun;
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
                ->each(function (Employment $employment) use ($run) {
                    $basicSalary = (float) $employment->basic_salary;

                    $run->lines()->create([
                        'employee_id' => $employment->employee_id,
                        'employment_id' => $employment->id,
                        'basic_salary' => $basicSalary,
                        'gross_pay' => $basicSalary,
                        'paye_amount' => $this->statutoryEngine->payeFor($basicSalary),
                        'nssf_employee_amount' => $this->statutoryEngine->nssfEmployeeFor($basicSalary),
                        'nssf_employer_amount' => $this->statutoryEngine->nssfEmployerFor($basicSalary),
                        'other_deductions' => 0,
                        'net_pay' => $basicSalary
                            - $this->statutoryEngine->payeFor($basicSalary)
                            - $this->statutoryEngine->nssfEmployeeFor($basicSalary),
                        'currency' => $employment->currency,
                    ]);
                });

            return $run;
        });
    }
}
