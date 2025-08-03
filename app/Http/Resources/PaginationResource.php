<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaginationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'total' => $this->total(),
            'from' => $this->firstItem(),
            'to' => $this->lastItem(),
            'per_page' => $this->perPage(),
            'current' => $this->currentPage(),
            'last_page' => $this->lastPage()
        ];
    }
}
