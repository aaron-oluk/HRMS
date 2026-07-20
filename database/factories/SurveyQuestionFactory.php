<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyQuestion>
 */
class SurveyQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'tenant_id' => fn (array $attributes) => Survey::find($attributes['survey_id'])->tenant_id,
            'text' => fake()->sentence().'?',
            'type' => 'rating',
            'order' => 0,
        ];
    }
}
