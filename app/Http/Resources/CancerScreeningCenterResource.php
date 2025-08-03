<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CancerScreeningCenterResource extends JsonResource
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
            'id' => $this->id ?? -1,
            'hospital_name' => $locale == 'en' ? $this->hospital_name_en : $this->hospital_name_ar,
            'phone_number' => $this->phone_number ?? '',
            'hospital_logo' => $this->hospital_logo_path ?? '',
            'country' => $locale == 'en' ?  $this->country->name_en : $this->country->name_ar,
             'country_code' => $this->country->country_code ?? '',

            'region' => $locale == 'en' ?  $this->region->name_en : $this->region->name_ar,
            'website' => $this->website ?? '',
            'google_map_link' => $this->google_map_link ?? '',
        ];
    }
}
