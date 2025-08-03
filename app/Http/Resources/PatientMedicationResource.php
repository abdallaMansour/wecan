<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientMedicationResource extends JsonResource
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
            'drug_name' => $this->drug_name ?? '',
            'frequency' => $this->frequency ?? '',
            'frequency_per' => $this->frequency_per ?? 'day',
            'instructions' => $this->instructions ?? '',
            'duration' => $this->duration ?? 0,
            'drug_image' => $this->drug_image_path ?? '',
            'show' => (bool) $this->show,
            'is_hospital' => (bool) $this->is_hospital, // Cast to boolean
            'hospital_id' => $this->hospital_id ?? null,
             'hospital_name' => $this->hospital_name ?? null,
        ];
    }
}
