<?php

namespace Database\Factories;

use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyResponseAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyResponseAnswer>
 */
class SurveyResponseAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_response_id' => SurveyResponse::factory(),
            'survey_question_id' => SurveyQuestion::factory(),
            'tenant_id' => fn (array $attributes) => SurveyResponse::find($attributes['survey_response_id'])->tenant_id,
            'rating_value' => fake()->numberBetween(1, 5),
        ];
    }
}
