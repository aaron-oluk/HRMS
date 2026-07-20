<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_anonymous' => ['nullable', 'boolean'],
            'closes_at' => ['nullable', 'date'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.text' => ['required', 'string', 'max:500'],
            'questions.*.type' => ['required', 'string', 'in:rating,text'],
        ];
    }
}
