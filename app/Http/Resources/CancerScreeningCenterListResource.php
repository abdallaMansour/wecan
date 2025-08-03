<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CancerScreeningCenterListResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = CancerScreeningCenterResource::collection($this->collection);
        $pagination = new PaginationResource($this);

        return [
            'items' => $data,
            'pagination' => $pagination
        ];
    }
}
