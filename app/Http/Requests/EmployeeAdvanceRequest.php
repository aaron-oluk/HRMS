<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeAdvanceRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'monthly_deduction' => ['required', 'numeric', 'min:0.01', 'lte:amount'],
            'reason' => ['nullable', 'string', 'max:255'],
            'issued_date' => ['required', 'date'],
        ];
    }
}
