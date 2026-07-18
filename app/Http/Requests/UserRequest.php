<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            'employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')->where('tenant_id', $this->user()->tenant_id),
                Rule::unique('users')->ignore($this->route('user')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:8'],
            'role' => ['required', 'in:HR Admin,HR Manager,HR Specialist,Department Manager,Team Lead,Auditor,Accountant,Employee,Executive'],
            'status' => ['required', 'in:active,invited,suspended'],
        ];
    }
}
