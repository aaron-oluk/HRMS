<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PositionRequest extends FormRequest
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
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where('tenant_id', $this->user()->tenant_id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
