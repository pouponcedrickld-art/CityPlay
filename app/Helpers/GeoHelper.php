<?php

namespace App\Helpers;

class GeoHelper
{
    /**
     * Calcul de distance entre deux points GPS (formule Haversine)
     * Retourne la distance en mètres
     */
    public static function haversineDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371000; // mètres
        //Convertit degrés → radians
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
    // Formule mathématique Haversine
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    // Distance finale en mètres
        return $earthRadius * $c;
    }

    /**
     * Vérifie si un joueur est dans le rayon d'un lieu
     */
    public static function estDansLeRayon(
        float $latJoueur,
        float $lonJoueur,
        float $latLieu,
        float $lonLieu,
        int $rayonMetres
    ): bool {
        return self::haversineDistance($latJoueur, $lonJoueur, $latLieu, $lonLieu) <= $rayonMetres;
    }
}