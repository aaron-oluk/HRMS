<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatutoryConfigRequest extends FormRequest
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
            'bands' => ['required', 'array', 'min:1'],
            'bands.*.floor' => ['required', 'numeric', 'min:0'],
            'bands.*.rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'paye_surcharge_floor' => ['required', 'numeric', 'min:0'],
            'paye_surcharge_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'nssf_employee_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'nssf_employer_rate' => ['required', 'numeric', 'min:0', 'max:1'],
        ];
    }
}
