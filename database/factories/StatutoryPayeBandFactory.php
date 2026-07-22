<?php

namespace Database\Factories;

use App\Models\StatutoryPayeBand;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StatutoryPayeBand>
 */
class StatutoryPayeBandFactory extends Factory
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
            'floor' => 0,
            'rate' => 0,
            'order' => 1,
        ];
    }
}
