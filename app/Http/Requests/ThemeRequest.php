<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('themes', 'slug')->ignore($this->route('theme')),
            ],
            'color_50' => ['required', 'string', 'max:20'],
            'color_100' => ['required', 'string', 'max:20'],
            'color_500' => ['required', 'string', 'max:20'],
            'color_600' => ['required', 'string', 'max:20'],
            'color_700' => ['required', 'string', 'max:20'],
            'color_800' => ['required', 'string', 'max:20'],
            'font_stack' => ['required', 'string', 'max:255'],
        ];
    }
}
