<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chambre' => $this->chambre->num,
            'client' => $this->email,
            'date_deb' => $this->date_deb,
            'date_fin' => $this->date_fin,
            'status' => $this->status,
            'type_chambre' => $this->typeChambre->name,
            'capacity' => $this->typeChambre->capacity,
            'nmb_per' => $this->nmb_per,
        ];
    }
}
