<?php

namespace Database\Factories;

use App\Models\Entity;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entity>
 */
class EntityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->unique()->company(),
            'registration_number' => fake()->bothify('REG-####??'),
            'tax_identification_number' => fake()->numerify('#########'),
            'nssf_employer_number' => fake()->numerify('NSSF#####'),
            'address' => fake()->address(),
            'currency' => 'UGX',
            'status' => 'active',
        ];
    }
}
