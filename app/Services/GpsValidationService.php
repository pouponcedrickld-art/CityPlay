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
    private const PRECISION_MINIMALE = 50;

    public function validerPosition(float $latJoueur, float $lngJoueur, ?float $precision, Lieu $lieu): array
    {
        // === LOG : Tentative de validation ===
        Log::channel('cityplay')->info('Tentative validation GPS', [
            'lieu_id' => $lieu->id,
            'lat_joueur' => $latJoueur,
            'lng_joueur' => $lngJoueur,
            'precision' => $precision,
        ]);

        // === VÉRIFICATION 0 : Lieu sans coordonnées en base ===
        if ($lieu->latitude === null || $lieu->longitude === null) {
            Log::channel('cityplay')->warning('Lieu sans coordonnées GPS', ['lieu_id' => $lieu->id]);

            return [
                'succes' => false,
                'message' => 'La zone de cette énigme n\'a pas de GPS configuré. Contactez l\'organisateur.',
                'erreur' => 'lieu_sans_gps',
                'distance' => null,
                'rayon' => $lieu->rayon_metres,
            ];
        }

        // === VÉRIFICATION 1 : GPS indisponible ===
        if (is_null($latJoueur) || is_null($lngJoueur)) {
            Log::channel('cityplay')->warning('GPS indisponible', ['lieu_id' => $lieu->id]);
            
            return [
                'succes' => false,
                'message' => 'GPS indisponible. Veuillez activer la géolocalisation.',
                'erreur' => 'gps_indisponible',
                'distance' => null,
                'rayon' => $lieu->rayon_metres,
            ];
        }

        // === VÉRIFICATION 2 : Précision trop faible ===
        if (!is_null($precision) && $precision > self::PRECISION_MINIMALE) {
            Log::channel('cityplay')->warning('Précision GPS insuffisante', [
                'lieu_id' => $lieu->id,
                'precision' => $precision,
            ]);
            
            return [
                'succes' => false,
                'message' => sprintf(
                    'Précision GPS insuffisante (%d m). Approchez-vous ou attendez un meilleur signal.',
                    $precision
                ),
                'erreur' => 'precision_insuffisante',
                'distance' => null,
                'rayon' => $lieu->rayon_metres,
                'precision' => $precision,
            ];
        }

        // === CALCUL DISTANCE ===
        $distance = $this->calculerDistanceHaversine(
            $latJoueur,
            $lngJoueur,
            (float) $lieu->latitude,
            (float) $lieu->longitude
        );

        $estDansLeRayon = $distance <= $lieu->rayon_metres;

        // === LOG : Résultat ===
        Log::channel('cityplay')->info('Résultat validation GPS', [
            'lieu_id' => $lieu->id,
            'distance' => round($distance, 1),
            'rayon' => $lieu->rayon_metres,
            'succes' => $estDansLeRayon,
        ]);

        if ($estDansLeRayon) {
            return [
                'succes' => true,
                'message' => 'Position validée ! Vous êtes sur la zone de l\'énigme.',
                'distance' => round($distance, 1),
                'rayon' => $lieu->rayon_metres,
                'precision' => $precision,
            ];
        }

        return [
            'succes' => false,
            'message' => sprintf(
                'Vous n\'êtes pas encore sur la zone de l\'énigme (environ %.0f m). Rapprochez-vous et réessayez.',
                $distance
            ),
            'erreur' => 'hors_rayon',
            'distance' => round($distance, 1),
            'rayon' => $lieu->rayon_metres,
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
