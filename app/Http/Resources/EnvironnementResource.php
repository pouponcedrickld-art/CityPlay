<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnvironnementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ville_id' => $this->ville_id,
            'nom' => $this->nom,
            'retention_profils_jours' => $this->retention_profils_jours,
            'duree_vie_lien_heures' => $this->duree_vie_lien_heures,
            'messages' => $this->messages,
            'regles' => $this->regles,
            'actif' => $this->actif,
            'ville' => new VilleResource($this->whenLoaded('ville')),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}