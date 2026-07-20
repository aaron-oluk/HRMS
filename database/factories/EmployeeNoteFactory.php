<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeNote>
 */
class EmployeeNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'title' => fake()->sentence(3),
            'body' => fake()->paragraph(),
        ];
    }
}
