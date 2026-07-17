<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeBankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeBankAccount>
 */
class EmployeeBankAccountFactory extends Factory
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
            'bank_name' => fake()->randomElement(['Stanbic Bank', 'Centenary Bank', 'DFCU Bank', 'Absa Bank Uganda']),
            'branch_name' => fake()->city().' Branch',
            'account_name' => fake()->name(),
            'account_number' => fake()->unique()->numerify('##########'),
            'is_primary' => true,
        ];
    }
}
