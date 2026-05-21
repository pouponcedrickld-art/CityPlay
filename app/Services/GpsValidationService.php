<?php

namespace App\Services;

use App\Models\Lieu;
use Illuminate\Support\Facades\Log;

/**
 * Service de validation GPS (Jour 3 - avec logs)
 */
class GpsValidationService
{
    private const RAYON_TERRE = 6371000;
    private const PRECISION_MINIMALE = 100; // Augmenté de 50 à 100 pour être plus tolérant

    public function validerPosition(float $latJoueur, float $lngJoueur, ?float $precision, Lieu $lieu): array
    {
        // ... (rest of the logs)
        Log::channel('cityplay')->info('Tentative validation GPS', [
            'lieu_id' => $lieu->id,
            'lat_joueur' => $latJoueur,
            'lng_joueur' => $lngJoueur,
            'precision' => $precision,
        ]);

        // === VÉRIFICATION 0 : Lieu sans coordonnées en base ===
        if ($lieu->latitude === null || $lieu->longitude === null) {
            return [
                'succes' => false,
                'message' => 'La zone de cette énigme n\'a pas de GPS configuré. Contactez l\'organisateur.',
                'erreur' => 'lieu_sans_gps',
            ];
        }

        // === VÉRIFICATION 1 : GPS indisponible ===
        if (is_null($latJoueur) || is_null($lngJoueur)) {
            return [
                'succes' => false,
                'message' => 'GPS indisponible. Veuillez activer la géolocalisation.',
                'erreur' => 'gps_indisponible',
            ];
        }

        // === VÉRIFICATION 2 : Précision trop faible ===
        // On ne bloque plus systématiquement si la précision est faible, 
        // on ajoute une marge d'erreur si le joueur est proche mais que le GPS est imprécis
        $margePrecision = 0;
        if (!is_null($precision) && $precision > self::PRECISION_MINIMALE) {
             Log::channel('cityplay')->warning('Précision GPS faible', ['precision' => $precision]);
             // Optionnel : on pourrait quand même laisser passer si la distance est très faible
        }

        // === CALCUL DISTANCE ===
        $distance = $this->calculerDistanceHaversine(
            $latJoueur,
            $lngJoueur,
            (float) $lieu->latitude,
            (float) $lieu->longitude
        );

        $rayon = $lieu->rayon_metres ?? 50; // Fallback à 50m
        
        // On accepte si distance <= rayon
        // OU si on est très proche (marge de 10m supplémentaire pour compenser les arrondis/imprécisions)
        $estDansLeRayon = $distance <= ($rayon + 10);

        Log::channel('cityplay')->info('Résultat validation GPS', [
            'lieu_id' => $lieu->id,
            'distance' => round($distance, 1),
            'rayon' => $rayon,
            'succes' => $estDansLeRayon,
        ]);

        if ($estDansLeRayon) {
            return [
                'succes' => true,
                'message' => 'Félicitations ! Vous avez trouvé le lieu.',
                'distance' => round($distance, 1),
                'rayon' => $rayon,
                'precision' => $precision,
            ];
        }

        return [
            'succes' => false,
            'message' => sprintf(
                'Vous n\'êtes pas encore tout à fait au bon endroit (environ %.0f m). Rapprochez-vous !',
                $distance
            ),
            'erreur' => 'hors_rayon',
            'distance' => round($distance, 1),
            'rayon' => $rayon,
            'precision' => $precision,
        ];
    }

    private function calculerDistanceHaversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2)
            + cos($lat1Rad) * cos($lat2Rad)
            * sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::RAYON_TERRE * $c;
    }

    public function formaterDistance(float $latFrom, float $lngFrom, float $latTo, float $lngTo): string
    {
        $distance = $this->calculerDistanceHaversine($latFrom, $lngFrom, $latTo, $lngTo);

        if ($distance >= 1000) {
            return round($distance / 1000, 1) . ' km';
        }

        return round($distance) . ' m';
    }
}
