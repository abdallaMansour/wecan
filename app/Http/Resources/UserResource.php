<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'token' => $this->token ?? '',
            'email' => $this->email ?? '',
            'name' => $this->name ?? '',
            'preferred_language' => $this->preferred_language ?? 'ar',
            'account_type' => $this->account_type ?? '',
            'hospital_id' => $this->hospital_id ?? 0,
        ];
    }
}
