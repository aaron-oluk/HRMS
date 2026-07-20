<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\OneOnOneMeeting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OneOnOneMeeting>
 */
class OneOnOneMeetingFactory extends Factory
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
            'manager_employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'scheduled_at' => now()->addWeek(),
            'status' => 'scheduled',
        ];
    }
}
