<?php

namespace Database\Factories;

use App\Models\AttendanceDay;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceDay>
 */
class AttendanceDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clockIn = fake()->dateTimeBetween('-1 week', 'now')->setTime(8, 0);
        $clockOut = (clone $clockIn)->modify('+8 hours 30 minutes');

        return [
            'employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'date' => $clockIn->format('Y-m-d'),
            'clock_in_at' => $clockIn,
            'clock_out_at' => $clockOut,
            'worked_minutes' => 510,
            'status' => 'present',
        ];
    }
}
