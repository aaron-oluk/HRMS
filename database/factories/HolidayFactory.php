<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
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
            'date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'name' => fake()->randomElement(['New Year', 'Labour Day', 'Independence Day', 'Christmas Day']),
        ];
    }
}
