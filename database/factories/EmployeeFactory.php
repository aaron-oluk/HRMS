<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
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
            'employee_number' => fake()->unique()->numerify('EMP-#####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'other_names' => null,
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
            'national_id_number' => fake()->unique()->numerify('CM#############'),
            'nssf_number' => fake()->unique()->numerify('NSSF#######'),
            'tin_number' => fake()->unique()->numerify('##########'),
            'phone' => fake()->unique()->numerify('+2567########'),
            'personal_email' => fake()->unique()->safeEmail(),
            'marital_status' => fake()->randomElement(['single', 'married']),
            'nationality' => 'Ugandan',
            'status' => 'active',
        ];
    }
}
