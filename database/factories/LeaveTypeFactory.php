<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_id' => Entity::factory(),
            'tenant_id' => fn (array $attributes) => Entity::find($attributes['entity_id'])->tenant_id,
            'name' => fake()->randomElement(['Annual Leave', 'Sick Leave', 'Unpaid Leave', 'Parental Leave']),
            'code' => fake()->unique()->bothify('LV-##'),
            'is_paid' => true,
            'requires_approval' => true,
            'default_days_per_year' => 21,
            'max_carry_forward_days' => 5,
            'status' => 'active',
        ];
    }
}
