<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
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
            'name' => 'Grade '.fake()->unique()->numberBetween(1, 20),
            'code' => fake()->unique()->bothify('GR-##'),
            'level' => fake()->numberBetween(1, 20),
            'min_salary' => fake()->numberBetween(500000, 2000000),
            'max_salary' => fake()->numberBetween(2000001, 8000000),
            'currency' => 'UGX',
            'status' => 'active',
        ];
    }
}
