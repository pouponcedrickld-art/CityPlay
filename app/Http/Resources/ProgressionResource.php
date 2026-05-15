<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgressionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'partie_id' => $this->partie_id,
            'user_id' => $this->user_id,
            'lieux_a_visiter' => $this->lieux_a_visiter,
            'lieux_decouverts' => $this->lieux_decouverts,
            'lieux_restants' => $this->lieux_restants,
            'enigme_courante' => new EnigmeResource($this->whenLoaded('enigmeCourante')),
            'temps_restant_minutes' => $this->temps_restant_minutes,
            'nb_enigmes_resolues' => $this->nb_enigmes_resolues,
        ];
    }
}