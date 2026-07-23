<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PerformanceGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerformanceGoal>
 */
class PerformanceGoalFactory extends Factory
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
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(10),
            'target_value' => 100,
            'current_value' => fake()->numberBetween(0, 100),
            'unit' => '%',
            'status' => 'on_track',
            'start_date' => now()->subMonth()->toDateString(),
            'due_date' => now()->addMonths(3)->toDateString(),
        ];
    }
}
