<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\CandidateComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateComment>
 */
class CandidateCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'candidate_id' => Candidate::factory(),
            'tenant_id' => fn (array $attributes) => Candidate::find($attributes['candidate_id'])->tenant_id,
            'body' => fake()->sentence(12),
        ];
    }
}
