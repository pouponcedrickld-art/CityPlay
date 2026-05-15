<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'environnement_id' => $this->environnement_id,
            'createur_id' => $this->createur_id,
            'team_id' => $this->team_id,
            'mode' => $this->mode,
            'code_liaison' => $this->code_liaison,
            'statut' => $this->statut,
            'parametres' => $this->parametres,
            'expire_at' => $this->expire_at?->format('Y-m-d H:i'),
            'started_at' => $this->started_at?->format('Y-m-d H:i'),
            'ended_at' => $this->ended_at?->format('Y-m-d H:i'),
            'environnement' => new EnvironnementResource($this->whenLoaded('environnement')),
        ];
    }
}