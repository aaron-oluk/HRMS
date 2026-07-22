<?php

namespace Database\Factories;

use App\Models\StatutorySetting;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StatutorySetting>
 */
class StatutorySettingFactory extends Factory
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
            'paye_surcharge_floor' => 10_000_000,
            'paye_surcharge_rate' => 0.10,
            'nssf_employee_rate' => 0.05,
            'nssf_employer_rate' => 0.10,
        ];
    }
}
