<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canViewIdentityNumbers = $request->user()?->can('employees.view-identity-numbers') ?? false;

        return [
            'id' => $this->id,
            'entity_id' => $this->entity_id,
            'employee_number' => $this->employee_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'other_names' => $this->other_names,
            'full_name' => $this->fullName(),
            'gender' => $this->gender,
            'phone' => $this->phone,
            'personal_email' => $this->personal_email,
            'marital_status' => $this->marital_status,
            'nationality' => $this->nationality,
            'status' => $this->status,
            'date_of_birth' => $this->when($canViewIdentityNumbers, $this->date_of_birth?->toDateString()),
            'national_id_number' => $this->when($canViewIdentityNumbers, $this->national_id_number),
            'nssf_number' => $this->when($canViewIdentityNumbers, $this->nssf_number),
            'tin_number' => $this->when($canViewIdentityNumbers, $this->tin_number),
            'current_employment' => EmploymentResource::make($this->whenLoaded('currentEmployment')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
