<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
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
            'name' => $locale == 'en' ? $this->name_en : $this->name,
            'contact_number' => $this->contact_number,
            'profession' => $locale == 'en' ? $this->profession_en : $this->profession_ar,
            'hospital' => $locale == 'en' ? $this->hospital_en : $this->hospital_ar,
            'profile_picture' => $this->profile_picture_path,
            'experience_years' => $this->experience_years
        ];
    }
}
