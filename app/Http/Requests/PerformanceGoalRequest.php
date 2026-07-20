<?php

namespace App\Http\Requests;

use App\Models\PerformanceGoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerformanceGoalRequest extends FormRequest
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
            'target_value' => ['nullable', 'numeric'],
            'current_value' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:32'],
            'status' => ['required', 'string', Rule::in(PerformanceGoal::STATUSES)],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
