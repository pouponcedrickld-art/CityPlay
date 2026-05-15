<?php

namespace App\Services;

use App\Models\Lieu;

/**
 * Service de validation GPS
 * 
 * Responsable de :
 * - Calculer la distance entre deux points GPS (Haversine)
 * - Vérifier si le joueur est dans le rayon de validation
 * - Gérer les cas d'erreur GPS (indisponible, précision faible)
 */
class GpsValidationService
{
    /**
     * Rayon de la Terre en mètres
     * Constante pour le calcul Haversine
     */
    private const RAYON_TERRE = 6371000;

    /**
     * Précision GPS minimale acceptable en mètres
     * Au-delà, on considère que la position est trop imprécise
     */
    private const PRECISION_MINIMALE = 50;

    /**
     * Valide la position GPS d'un joueur contre un lieu cible
     * 
     * @param float $latJoueur Latitude du joueur
     * @param float $lngJoueur Longitude du joueur
     * @param float $precision Précision GPS en mètres (optionnel)
     * @param Lieu $lieu Le lieu à valider
     * @return array Résultat de la validation
     */
    public function validerPosition(float $latJoueur, float $lngJoueur, ?float $precision, Lieu $lieu): array
    {
        // === VÉRIFICATION 1 : GPS indisponible ===
        if (is_null($latJoueur) || is_null($lngJoueur)) {
            return [
                'succes' => false,
                'message' => 'GPS indisponible. Veuillez activer la géolocalisation.',
                'erreur' => 'gps_indisponible',
                'distance' => null,
                'rayon' => $lieu->rayon,
            ];
        }

        // === VÉRIFICATION 2 : Précision trop faible ===
        if (!is_null($precision) && $precision > self::PRECISION_MINIMALE) {
            return [
                'succes' => false,
                'message' => sprintf(
                    'Précision GPS insuffisante (%d m). Approchez-vous ou attendez un meilleur signal.',
                    $precision
                ),
                'erreur' => 'precision_insuffisante',
                'distance' => null,
                'rayon' => $lieu->rayon,
                'precision' => $precision,
            ];
        }

        // === CALCUL DISTANCE (Haversine) ===
        $distance = $this->calculerDistanceHaversine(
            $latJoueur,
            $lngJoueur,
            $lieu->lat,
            $lieu->lng
        );

        // === VÉRIFICATION 3 : Dans le rayon ? ===
        $estDansLeRayon = $distance <= $lieu->rayon;

        if ($estDansLeRayon) {
            return [
                'succes' => true,
                'message' => 'Position validée ! Vous êtes sur le lieu.',
                'distance' => round($distance, 1), // Arrondi à 1 décimale
                'rayon' => $lieu->rayon,
                'precision' => $precision,
            ];
        }

        // === HORS RAYON ===
        return [
            'succes' => false,
            'message' => sprintf(
                'Vous êtes à %.0f mètres du lieu. Approchez-vous encore (rayon : %d m).',
                $distance,
                $lieu->rayon
            ),
            'erreur' => 'hors_rayon',
            'distance' => round($distance, 1),
            'rayon' => $lieu->rayon,
            'precision' => $precision,
        ];
    }

    /**
     * Algorithme Haversine
     * Calcule la distance orthodromique entre deux points GPS
     * 
     * Formule : a = sin²(Δφ/2) + cos(φ1) * cos(φ2) * sin²(Δλ/2)
     *           c = 2 * atan2(√a, √(1−a))
     *           d = R * c
     * 
     * @param float $lat1 Latitude point 1 (joueur)
     * @param float $lng1 Longitude point 1 (joueur)
     * @param float $lat2 Latitude point 2 (lieu)
     * @param float $lng2 Longitude point 2 (lieu)
     * @return float Distance en mètres
     */
    private function calculerDistanceHaversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        // Conversion des degrés en radians
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        // Formule Haversine
        $a = sin($deltaLat / 2) * sin($deltaLat / 2)
            + cos($lat1Rad) * cos($lat2Rad)
            * sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Distance = rayon * angle central
        return self::RAYON_TERRE * $c;
    }

    /**
     * Calcule la distance à vol d'oiseau (affichage résumé fin de partie)
     * 
     * @param float $latFrom Latitude départ
     * @param float $lngFrom Longitude départ
     * @param float $latTo Latitude arrivée
     * @param float $lngTo Longitude arrivée
     * @return string Distance formatée (ex: "1.2 km" ou "450 m")
     */
    public function formaterDistance(float $latFrom, float $lngFrom, float $latTo, float $lngTo): string
    {
        $distance = $this->calculerDistanceHaversine($latFrom, $lngFrom, $latTo, $lngTo);

        if ($distance >= 1000) {
            return round($distance / 1000, 1) . ' km';
        }

        return round($distance) . ' m';
    }
}