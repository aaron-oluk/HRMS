<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmploymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('employments.manage');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'entity_id' => ['required', Rule::exists('entities', 'id')->where('tenant_id', $tenantId)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('tenant_id', $tenantId)],
            'department_id' => ['required', Rule::exists('departments', 'id')->where('tenant_id', $tenantId)],
            'position_id' => ['required', Rule::exists('positions', 'id')->where('tenant_id', $tenantId)],
            'grade_id' => ['nullable', Rule::exists('grades', 'id')->where('tenant_id', $tenantId)],
            'reporting_to_employee_id' => ['nullable', Rule::exists('employees', 'id')->where('tenant_id', $tenantId)],
            'employment_type' => ['required', 'in:full_time,part_time,contract,intern'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'effective_from' => ['required', 'date'],
            'reason' => ['required', 'in:initial,promotion,transfer,salary_review,probation,demotion,other'],
        ];
    }
}
