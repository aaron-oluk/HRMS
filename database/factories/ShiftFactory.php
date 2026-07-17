<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
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
            'name' => 'Day Shift',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'break_minutes' => 60,
            'status' => 'active',
        ];
    }
}
