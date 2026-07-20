<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignableDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signer_user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
