<?php

namespace Database\Factories;

use App\Actions\Tenancy\SeedDefaultStatutoryConfig;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'status' => 'active',
            'timezone' => 'Africa/Kampala',
            'currency' => 'UGX',
            'structure' => 'simple',
        ];
    }

    public function configure(): static
    {
        // Every tenant needs its own statutory config to run payroll — App\Actions\Tenancy\
        // CreateTenant does this in production; factory-created tenants (tests, tinker) need
        // the same so StatutoryEngine has something to load.
        return $this->afterCreating(function (Tenant $tenant): void {
            app(SeedDefaultStatutoryConfig::class)->handle($tenant);
        });
    }
}
