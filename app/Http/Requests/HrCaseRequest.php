<?php

namespace App\Http\Requests;

use App\Models\HrCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HrCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::in(HrCase::CATEGORIES)],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ];
    }
}
