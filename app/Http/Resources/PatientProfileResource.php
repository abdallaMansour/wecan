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
        $lang = $this->preferred_language ?? 'ar';
        $name_key = $lang == 'ar' ? 'name_ar' : 'name_en';

        return [
            'id' => $this->id ?? -1,
            'email' => $this->email ?? '',
            'name' => $this->name ?? '',
            'preferred_language' => $lang,
            'country_name' => $this->country->$name_key ?? '',
            'country_id' => $this->country_id ?? -1,
            'cancer_name' => $this->cancer?->$name_key ?? '',
            'cancer_id' => $this->cancer_id ?? -1,
        ];
    }
}
