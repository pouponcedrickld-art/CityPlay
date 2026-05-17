<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource ProgressionPartie
 * 
 * Formate la réponse JSON pour l'API de progression.
 * Contrôle exactement ce qui est exposé au front.
 */
class ProgressionResource extends JsonResource
{
    /**
     * Transforme la ressource en tableau JSON
     * 
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Identifiants
            'id' => $this->id,
            'partie_id' => $this->partie_id,

            // Lieux (simplifiés pour le front)
            'lieux_a_visiter' => $this->lieux_a_visiter,
            'lieux_decouverts' => $this->lieux_decouverts,
            'lieux_restants' => $this->lieux_restants,
            'nb_lieux_total' => count($this->lieux_a_visiter ?? []),
            'nb_lieux_decouverts' => count($this->lieux_decouverts ?? []),

            // Énigme courante (chargée avec eager loading)
            'enigme_courante' => $this->whenLoaded('enigmeCourante', function () {
                return [
                    'id' => $this->enigmeCourante->id,
                    'type' => $this->enigmeCourante->type,
                    'texte' => $this->enigmeCourante->texte,
                    'image' => $this->enigmeCourante->image,
                    'lieu' => [
                        'id' => $this->enigmeCourante->lieu->id,
                        'nom' => $this->enigmeCourante->lieu->nom,
                        'ordre' => $this->enigmeCourante->lieu->ordre,
                    ],
                ];
            }),

            // Temps et score
            'temps_restant' => $this->temps_restant,
            'temps_restant_formate' => $this->formaterTemps($this->temps_restant),
            'score' => $this->score,

            // Statut
            'statut' => $this->statut,
            'est_en_cours' => $this->statut === 'en_cours',
            'est_en_pause' => $this->statut === 'pause',
            'est_terminee' => $this->statut === 'terminee',

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Formate les secondes en MM:SS
     */
    private function formaterTemps(?int $secondes): ?string
    {
        if (is_null($secondes)) {
            return null;
        }

        $minutes = floor($secondes / 60);
        $secs = $secondes % 60;

        return sprintf('%02d:%02d', $minutes, $secs);
    }
}