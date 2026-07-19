<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayrollRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity_id' => ['required', 'integer', Rule::exists('entities', 'id')->where('tenant_id', $this->user()->tenant_id)],
            'period_month' => ['required', 'date'],
        ];
    }
}
