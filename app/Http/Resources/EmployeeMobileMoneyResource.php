<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeMobileMoneyResource extends JsonResource
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
            'provider' => $this->provider,
            'account_name' => $this->account_name,
            'phone_number' => $this->when($canViewSensitive, $this->phone_number),
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at,
        ];
    }
}
