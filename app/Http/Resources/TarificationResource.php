<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TarificationResource extends JsonResource
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
            'prix' => $this->prix,
            'saison' => $this->saison,
            'date_deb' => Carbon::parse($this->date_deb)->translatedFormat('d F'),
            'date_fin' => Carbon::parse($this->date_fin)->translatedFormat('d F'),
            'date_start' => $this->date_deb,
            'date_end' => $this->date_fin,
            'status' => $this->status,
            'type_chambre' => $this->typeChambre->name,
            'type_chambre_id' => $this->typeChambre->id,
            'hotel_id' => $this->typeChambre->hotel_id,
        ];
    }
}
