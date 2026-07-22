<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeDeduction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDeduction>
 */
class EmployeeDeductionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'label' => fake()->randomElement(['Uniform cost', 'Damaged equipment', 'Union dues', 'Loan repayment']),
            'amount' => fake()->numberBetween(10_000, 200_000),
            'frequency' => fake()->randomElement(EmployeeDeduction::FREQUENCIES),
            'status' => 'active',
            'effective_date' => now()->toDateString(),
        ];
    }
}
