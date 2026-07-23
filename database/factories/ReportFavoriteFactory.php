<?php

namespace Database\Factories;

use App\Models\ReportFavorite;
use App\Models\User;
use App\Support\Reports\ReportCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportFavorite>
 */
class ReportFavoriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tenant_id' => fn (array $attributes) => User::find($attributes['user_id'])->tenant_id,
            'report_key' => fake()->randomElement(ReportCatalog::keys()),
        ];
    }
}
