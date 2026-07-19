<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PayrollRunLine;
use App\Support\Payroll\StatutoryEngine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollRunLine>
 */
class PayrollRunLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $basicSalary = fake()->numberBetween(500000, 5000000);
        $engine = new StatutoryEngine;

        return [
            'payroll_run_id' => PayrollRun::factory(),
            'employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'employment_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->currentEmployment?->id,
            'basic_salary' => $basicSalary,
            'gross_pay' => $basicSalary,
            'paye_amount' => $engine->payeFor($basicSalary),
            'nssf_employee_amount' => $engine->nssfEmployeeFor($basicSalary),
            'nssf_employer_amount' => $engine->nssfEmployerFor($basicSalary),
            'other_deductions' => 0,
            'net_pay' => $basicSalary - $engine->payeFor($basicSalary) - $engine->nssfEmployeeFor($basicSalary),
            'currency' => 'UGX',
        ];
    }
}
