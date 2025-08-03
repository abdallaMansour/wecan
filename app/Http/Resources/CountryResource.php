<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
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
            'name' => $locale == 'en' ? $this->name_en : $this->name_ar,
            
            'country_code' => $this->country->country_code ?? '',
        ];
    }
}
