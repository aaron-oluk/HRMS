<?php

namespace Database\Factories;

use App\Models\EmployeeDeduction;
use App\Models\PayrollRunLine;
use App\Models\PayrollRunLineDeduction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollRunLineDeduction>
 */
class PayrollRunLineDeductionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payroll_run_line_id' => PayrollRunLine::factory(),
            'tenant_id' => fn (array $attributes) => PayrollRunLine::find($attributes['payroll_run_line_id'])->tenant_id,
            'source_type' => EmployeeDeduction::class,
            'source_id' => EmployeeDeduction::factory(),
            'label' => fake()->words(2, true),
            'amount' => fake()->numberBetween(10_000, 100_000),
        ];
    }
}
