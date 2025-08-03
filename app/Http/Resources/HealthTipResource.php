<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HealthTipResource extends JsonResource
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
            'publish_datetime' => $this->publish_datetime ?? '',
            'title_ar' => $this->title_ar ?? '',
            'title_en' =>  $this->title_en ?? '',
            'details_ar' => $this->details_ar ?? '',
            'details_en' =>  $this->details_en ?? '',
            'link' => $this->link ?? '',
            'tip_type' =>  $this->tip_type ?? '',
            'doctor_name' => $this->user->name ??  '',
            'doctor_image' => $this->user->profile_picture_path ??  '',
             'visible' => $this->visible ? true : false, 
            'attachments' => $this->attachments_paths ?? []
        ];
    }
}
