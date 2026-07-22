<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeWarning;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeWarning>
 */
class EmployeeWarningFactory extends Factory
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
            'severity' => fake()->randomElement(EmployeeWarning::SEVERITIES),
            'reason' => fake()->sentence(),
            'issued_at' => now()->toDateString(),
            'status' => 'active',
        ];
    }
}
