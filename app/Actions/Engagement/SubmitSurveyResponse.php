<?php

namespace App\Actions\Engagement;

use App\Models\Employee;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitSurveyResponse
{
    /**
     * @param  array<int, array{question_id: int, rating_value?: int|null, text_value?: string|null}>  $answers
     */
    public function handle(Survey $survey, Employee $employee, array $answers): SurveyResponse
    {
        if ($survey->status !== 'active') {
            throw ValidationException::withMessages([
                'survey' => 'This survey is no longer accepting responses.',
            ]);
        }

        if ($survey->responses()->where('employee_id', $employee->id)->exists()) {
            throw ValidationException::withMessages([
                'survey' => 'You have already responded to this survey.',
            ]);
        }

        return DB::transaction(function () use ($survey, $employee, $answers) {
            $response = $survey->responses()->create([
                'employee_id' => $employee->id,
                'submitted_at' => now(),
            ]);

            foreach ($answers as $answer) {
                $response->answers()->create([
                    'survey_question_id' => $answer['question_id'],
                    'rating_value' => $answer['rating_value'] ?? null,
                    'text_value' => $answer['text_value'] ?? null,
                ]);
            }

            return $response;
        });
    }
}
