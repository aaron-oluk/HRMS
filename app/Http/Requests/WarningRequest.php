<?php

namespace App\Http\Requests;

use App\Models\EmployeeWarning;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'severity' => ['required', 'string', Rule::in(EmployeeWarning::SEVERITIES)],
            'reason' => ['required', 'string', 'max:2000'],
            'issued_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after:issued_at'],
        ];
    }
}
