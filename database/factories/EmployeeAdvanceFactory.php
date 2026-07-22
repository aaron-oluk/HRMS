<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeAdvance>
 */
class EmployeeAdvanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->numberBetween(100_000, 2_000_000);

        return [
            'employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'amount' => $amount,
            'monthly_deduction' => (int) round($amount / 4),
            'balance_remaining' => $amount,
            'reason' => fake()->sentence(),
            'issued_date' => now()->toDateString(),
            'status' => 'active',
        ];
    }
}
