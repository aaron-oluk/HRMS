<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
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
            'parent_department_id' => null,
            'name' => ucwords(fake()->unique()->words(2, true)),
            'code' => fake()->unique()->bothify('DEPT-###'),
            'status' => 'active',
        ];
    }
}
