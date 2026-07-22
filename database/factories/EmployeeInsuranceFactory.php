<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeInsurance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeInsurance>
 */
class EmployeeInsuranceFactory extends Factory
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
            'provider' => fake()->company(),
            'policy_number' => strtoupper(fake()->bothify('POL-####??')),
            'type' => fake()->randomElement(EmployeeInsurance::TYPES),
            'coverage_amount' => fake()->numberBetween(1_000_000, 20_000_000),
            'start_date' => now()->subYear()->toDateString(),
            'status' => 'active',
        ];
    }
}
