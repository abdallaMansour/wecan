<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorProfileResource extends JsonResource
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
            'profession_ar' => $this->profession_ar ?? '',
            'profession_en' => $this->profession_en ?? '',
            'hospital_ar' => $this->hospital_ar ?? '',
            'hospital_en' => $this->hospital_en ?? '',
            'contact_number' => $this->contact_number ?? '',
            'experience_years' => $this->experience_years ?? 0,
            'profile_picture' => $this->profile_picture_path,
            'show_info_to_patients' => $this->show_info_to_patients ?? false
        ];
    }
}
