<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        return [
            'id' => $this->id,
            'hospital_name' => $this->hospital_name,
            'hospital_logo' => $this->hospital_logo,
            'user_name' => $this->user_name,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'country' => $this->country->name, // Assuming the country relation is loaded
            'city' => $this->city,
        ];
    }
}