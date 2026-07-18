<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeBankAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canViewBankDetails = $request->user()?->can('employees.view-bank-details') ?? false;

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'bank_name' => $this->bank_name,
            'branch_name' => $this->branch_name,
            'account_name' => $this->account_name,
            'account_number' => $this->when($canViewBankDetails, $this->account_number),
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at,
        ];
    }
}
