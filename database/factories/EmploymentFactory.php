<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employment>
 */
class EmploymentFactory extends Factory
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
            'entity_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->entity_id,
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'branch_id' => null,
            'department_id' => fn (array $attributes) => Department::factory()->create([
                'entity_id' => $attributes['entity_id'],
                'tenant_id' => $attributes['tenant_id'],
            ])->id,
            'position_id' => fn (array $attributes) => Position::factory()->create([
                'entity_id' => $attributes['entity_id'],
                'tenant_id' => $attributes['tenant_id'],
            ])->id,
            'grade_id' => null,
            'reporting_to_employee_id' => null,
            'employment_type' => 'full_time',
            'basic_salary' => fake()->numberBetween(500000, 5000000),
            'currency' => 'UGX',
            'effective_from' => now()->subMonths(fake()->numberBetween(1, 24))->toDateString(),
            'effective_to' => null,
            'status' => 'active',
            'reason' => 'initial',
        ];
    }
}
