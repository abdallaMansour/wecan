<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CharityResource extends JsonResource
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
            'country' => $locale == 'en' ?  $this->country->name_en : $this->country->name_ar,
            'country_id' =>  $this->country_id ?? -1,
          'country_code' => $this->country->country_code,

            'charity_logo' => $locale == 'en' ? $this->charity_logo_en_path ?? '' : $this->charity_logo_ar_path ?? '',
            'charity_name' => $locale == 'en' ? $this->charity_name_en : $this->charity_name_ar,
            'donation_details' => DonationResource::collection($this->donations ?? [])
        ];
    }
}
