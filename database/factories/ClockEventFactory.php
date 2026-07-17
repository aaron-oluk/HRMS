<?php

namespace Database\Factories;

use App\Models\ClockEvent;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClockEvent>
 */
class ClockEventFactory extends Factory
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
            'type' => 'clock_in',
            'occurred_at' => now(),
            'source' => 'web',
        ];
    }

    public function clockOut(): static
    {
        return $this->state(['type' => 'clock_out']);
    }
}
