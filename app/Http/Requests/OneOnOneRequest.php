<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OneOnOneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'scheduled_at' => ['required', 'date'],
            'agenda' => ['nullable', 'string'],
        ];
    }
}
