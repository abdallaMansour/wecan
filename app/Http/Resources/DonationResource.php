<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
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
            'donation_value' => $this->donation_value ?? '',
            'sms_code' => $this->sms_code ?? '',
            'message_code' => $this->message_code ?? '',
            'telecom_logo' => $this->telecom_logo_path ?? '',
            'country_code' => $this->country_code ?? ''
        ];
    }
}
