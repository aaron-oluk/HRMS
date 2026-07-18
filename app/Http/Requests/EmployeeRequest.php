<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
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
            'entity_id' => [
                'required',
                Rule::exists('entities', 'id')->where('tenant_id', $this->user()->tenant_id),
            ],
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees')
                    ->where('tenant_id', $this->user()->tenant_id)
                    ->ignore($this->route('employee')),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'other_names' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'national_id_number' => ['nullable', 'string', 'max:100'],
            'nssf_number' => ['nullable', 'string', 'max:100'],
            'tin_number' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,on_leave,suspended,exited'],
        ];
    }
}
