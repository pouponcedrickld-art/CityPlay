<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LieuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'environnement_id' => $this->environnement_id,
            'ordre' => $this->ordre,
            'nom' => $this->nom,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'rayon_metres' => $this->rayon_metres,
            'description' => $this->description,
            'actif' => $this->actif,
            'photos' => PhotoLieuResource::collection($this->whenLoaded('photos')),
            'enigmes' => EnigmeResource::collection($this->whenLoaded('enigmes')),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}