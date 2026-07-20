<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_number' => ['required', 'integer', 'min:0'],
            'x' => ['required', 'numeric', 'min:0', 'max:1'],
            'y' => ['required', 'numeric', 'min:0', 'max:1'],
            'width' => ['required', 'numeric', 'min:0.01', 'max:1'],
            'height' => ['required', 'numeric', 'min:0.01', 'max:1'],
        ];
    }
}
