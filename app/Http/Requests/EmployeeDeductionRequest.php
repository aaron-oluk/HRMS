<?php

namespace App\Http\Requests;

use App\Models\EmployeeDeduction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeDeductionRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'frequency' => ['required', 'string', Rule::in(EmployeeDeduction::FREQUENCIES)],
            'effective_date' => ['required', 'date'],
        ];
    }
}
