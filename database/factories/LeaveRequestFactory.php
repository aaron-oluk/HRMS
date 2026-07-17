<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+'.fake()->numberBetween(0, 4).' days');

        return [
            'employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'leave_type_id' => fn (array $attributes) => LeaveType::factory()->create([
                'tenant_id' => $attributes['tenant_id'],
                'entity_id' => Employee::find($attributes['employee_id'])->entity_id,
            ])->id,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'days' => $start->diff($end)->days + 1,
            'reason' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
