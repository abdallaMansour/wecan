<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientNoteResource extends JsonResource
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
            'title' => $this->title ?? '',
            'datetime' => $this->datetime ?? '',
            'notes' => $this->notes ?? '',
            'attachments' => $this->attachments_paths ?? [],
             'show' => (bool) $this->show,
        ];
    }
}
