<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmploymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canViewSalary = $request->user()?->can('employees.view-salary') ?? false;

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'entity_id' => $this->entity_id,
            'branch_id' => $this->branch_id,
            'department_id' => $this->department_id,
            'position_id' => $this->position_id,
            'grade_id' => $this->grade_id,
            'reporting_to_employee_id' => $this->reporting_to_employee_id,
            'employment_type' => $this->employment_type,
            'basic_salary' => $this->when($canViewSalary, $this->basic_salary),
            'currency' => $this->currency,
            'effective_from' => $this->effective_from?->toDateString(),
            'effective_to' => $this->effective_to?->toDateString(),
            'status' => $this->status,
            'reason' => $this->reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
