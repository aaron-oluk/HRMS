<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeCompensationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeCompensationItem>
 */
class EmployeeCompensationItemFactory extends Factory
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
            'category' => fake()->randomElement(['allowance', 'benefit']),
            'name' => fake()->randomElement(['Transport', 'Meal', 'Internet', 'Health Insurance', 'Life Insurance']),
            'amount' => fake()->numberBetween(20_000, 300_000),
        ];
    }

    public function allowance(): static
    {
        return $this->state(['category' => 'allowance']);
    }

    public function benefit(): static
    {
        return $this->state(['category' => 'benefit']);
    }
}
