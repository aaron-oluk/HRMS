<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyResponse>
 */
class SurveyResponseFactory extends Factory
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
            'employee_id' => Employee::factory(),
            'tenant_id' => fn (array $attributes) => Employee::find($attributes['employee_id'])->tenant_id,
            'submitted_at' => now(),
        ];
    }
}
