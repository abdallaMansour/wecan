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
        return [
            'id' => $this->id ?? -1,
            'session_number' =>  $this->session_number ?? '',
            'session_datetime' =>  $this->session_datetime ?? '',
            'instructions' =>  $this->instructions ?? '',
            'notes' =>  $this->notes ?? '',
            'show' => (bool) $this->show,
            'is_hospital' => (bool) $this->is_hospital, // Cast to boolean
            'hospital_id' => $this->hospital_id ?? null,
             'hospital_name' => $this->hospital_name ?? null,
        ];
    }
}
