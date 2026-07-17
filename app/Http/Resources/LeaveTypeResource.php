<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entity_id' => $this->entity_id,
            'name' => $this->name,
            'code' => $this->code,
            'is_paid' => $this->is_paid,
            'requires_approval' => $this->requires_approval,
            'default_days_per_year' => $this->default_days_per_year,
            'max_carry_forward_days' => $this->max_carry_forward_days,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
