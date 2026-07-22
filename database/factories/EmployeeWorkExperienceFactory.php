<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeWorkExperience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeWorkExperience>
 */
class EmployeeWorkExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-10 years', '-2 years');

        return [
            'employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'company_name' => fake()->company(),
            'job_title' => fake()->jobTitle(),
            'start_date' => $startDate,
            'end_date' => fake()->dateTimeBetween($startDate, '-1 year'),
        ];
    }
}
