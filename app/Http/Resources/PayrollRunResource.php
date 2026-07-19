<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entity_id' => $this->entity_id,
            'entity_name' => $this->whenLoaded('entity', fn () => $this->entity->name),
            'period_month' => $this->period_month?->toDateString(),
            'status' => $this->status,
            'approved_at' => $this->approved_at,
            'disbursed_at' => $this->disbursed_at,
            'created_at' => $this->created_at,
        ];
    }
}
