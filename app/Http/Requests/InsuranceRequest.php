<?php

namespace App\Http\Requests;

use App\Models\EmployeeInsurance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InsuranceRequest extends FormRequest
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
            'provider' => ['required', 'string', 'max:255'],
            'policy_number' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(EmployeeInsurance::TYPES)],
            'coverage_amount' => ['nullable', 'numeric', 'min:0'],
            'dependents_covered' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
        ];
    }
}
