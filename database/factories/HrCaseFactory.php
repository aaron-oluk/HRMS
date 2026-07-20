<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\HrCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HrCase>
 */
class HrCaseFactory extends Factory
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
            'category' => fake()->randomElement(HrCase::CATEGORIES),
            'subject' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => 'open',
        ];
    }
}
