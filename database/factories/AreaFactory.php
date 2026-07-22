<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
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
            'name' => fake()->city().' Region',
            'code' => fake()->unique()->bothify('AR-###'),
            'status' => 'active',
        ];
    }
}
