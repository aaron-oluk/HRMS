<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeerFeedbackNominationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reviewer_employee_id' => ['required', 'integer', 'exists:employees,id'],
        ];
    }
}
