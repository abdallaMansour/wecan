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
        if ($this->logUser?->account_type === 'user') {
            $logUser = $this->logUser?->parent?->account_type;
        } else {
            $logUser = $this->logUser?->account_type;
        }

        return [
            'id' => $this->id ?? -1,
            'drug_name' => $this->drug_name ?? '',
            'frequency' => $this->frequency ?? '',
            'frequency_per' => $this->frequency_per ?? 'day',
            'instructions' => $this->instructions ?? '',
            'duration' => $this->duration ?? 0,
            'drug_image' => $this->drug_image_path ?? '',
            'show' => (bool) $this->show,
            'is_hospital' => (bool) $logUser === 'hospital',
            'hospital_id' => $logUser === 'hospital' ? $this->logUser?->hospital_id ?? null : null,
            'hospital_name' => $logUser === 'hospital' ? $this->logUser?->hospital?->hospital_name ?? null : null,
            'is_doctor' => (bool) $logUser === 'doctor',
            'doctor_id' => $logUser === 'doctor' ? $this->logUser?->id ?? null : null,
            'doctor_name' => $logUser === 'doctor' ? $this->logUser?->name ?? null : null,
        ];
    }
}
