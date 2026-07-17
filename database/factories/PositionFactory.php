<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
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
            'department_id' => null,
            'title' => fake()->unique()->jobTitle(),
            'code' => fake()->unique()->bothify('POS-###'),
            'status' => 'active',
        ];
    }
}
