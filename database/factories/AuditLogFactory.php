<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'auditable_type' => Employee::class,
            'auditable_id' => Employee::factory(),
            'action' => 'created',
            'field' => 'status',
            'old_value' => null,
            'new_value' => 'active',
            'request_id' => fake()->uuid(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
