<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobRequisition>
 */
class JobRequisitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'entity_id' => fn (array $attributes) => Department::find($attributes['department_id'])->entity_id,
            'tenant_id' => fn (array $attributes) => Department::find($attributes['department_id'])->tenant_id,
            'position_id' => fn (array $attributes) => Position::factory()->create([
                'entity_id' => $attributes['entity_id'],
                'tenant_id' => $attributes['tenant_id'],
            ])->id,
            'title' => fake()->jobTitle(),
            'headcount' => fake()->numberBetween(1, 3),
            'status' => 'open',
            'description' => fake()->paragraph(),
            'opened_at' => now(),
        ];
    }
}
