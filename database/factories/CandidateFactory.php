<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\JobRequisition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_requisition_id' => JobRequisition::factory(),
            'tenant_id' => fn (array $attributes) => JobRequisition::find($attributes['job_requisition_id'])->tenant_id,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'source' => fake()->randomElement(['referral', 'job board', 'linkedin', 'walk-in']),
            'status' => 'applied',
        ];
    }
}
