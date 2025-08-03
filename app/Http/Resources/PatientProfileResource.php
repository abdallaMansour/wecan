<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientProfileResource extends JsonResource
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
            'email' => $this->email ?? '',
            'name' => $this->name ?? '',
            'preferred_language' => $this->preferred_language ?? 'ar',
            'country_name' => $this->country->name_ar ?? '',
            'country_id' => $this->country_id ?? -1

        ];
    }
}
