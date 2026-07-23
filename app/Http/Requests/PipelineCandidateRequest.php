<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PipelineCandidateRequest extends FormRequest
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
        $tenantId = $this->user()->tenant_id;

        return [
            'job_requisition_id' => ['required', 'integer', Rule::exists('job_requisitions', 'id')->where('tenant_id', $tenantId)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'source' => ['nullable', 'string', 'max:64'],
        ];
    }
}
