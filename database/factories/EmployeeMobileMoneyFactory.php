<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeMobileMoney;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeMobileMoney>
 */
class EmployeeMobileMoneyFactory extends Factory
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
            'provider' => fake()->randomElement(['mtn_momo', 'airtel_money']),
            'phone_number' => fake()->unique()->numerify('+2567########'),
            'account_name' => fake()->name(),
            'is_primary' => true,
        ];
    }
}
