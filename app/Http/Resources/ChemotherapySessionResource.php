<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChemotherapySessionResource extends JsonResource
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
            'session_number' =>  $this->session_number ?? '',
            'session_datetime' =>  $this->session_datetime ?? '',
            'instructions' =>  $this->instructions ?? '',
            'notes' =>  $this->notes ?? '',
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
