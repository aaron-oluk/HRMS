<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\OvertimeRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OvertimeRequest>
 */
class OvertimeRequestFactory extends Factory
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
            'date' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
            'hours' => fake()->randomFloat(2, 1, 4),
            'reason' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
