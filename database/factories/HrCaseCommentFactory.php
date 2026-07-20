<?php

namespace Database\Factories;

use App\Models\HrCase;
use App\Models\HrCaseComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HrCaseComment>
 */
class HrCaseCommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hr_case_id' => HrCase::factory(),
            'tenant_id' => fn (array $attributes) => HrCase::find($attributes['hr_case_id'])->tenant_id,
            'author_user_id' => User::factory(),
            'body' => fake()->sentence(),
            'is_internal' => false,
        ];
    }
}
