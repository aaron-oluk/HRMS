<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
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
            'name' => fake()->city().' Branch',
            'code' => fake()->unique()->bothify('BR-###'),
            'address' => fake()->address(),
            'status' => 'active',
        ];
    }
}
