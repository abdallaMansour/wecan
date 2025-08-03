<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientFoodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? -1,
            'food_name' => $this->food_name ?? '',
            'instructions' => $this->instructions ?? '',
            'notes' => $this->notes ?? '',
            'attachments' => $this->attachments_paths ?? [],
             'show' => (bool) $this->show,
            'is_hospital' => (bool) $this->is_hospital, // Cast to boolean
            'hospital_id' => $this->hospital_id ?? null,

        ];
    }
}
