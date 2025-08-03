<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientHealthReportResource extends JsonResource
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
            'title' => $this->title ?? '',
            'doctor_name' => $this->doctor_name ?? '',
            'datetime' => $this->datetime ?? '',
            'instructions' => $this->instructions ?? '',
            'notes' => $this->notes ?? '',
            'attachments' => $this->attachments_paths ?? [],
            'show' => (bool) $this->show,
             'is_hospital' => (bool) $this->is_hospital, // Cast to boolean
            'hospital_id' => $this->hospital_id ?? null,
             'hospital_name' => $this->hospital_name ?? null,
        ];
    }
}
