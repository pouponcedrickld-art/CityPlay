<?php

namespace App\Services;

use Illuminate\Support\Collection;

class GeoService
{
    /**
     * Distance en km entre deux points GPS (formule de Haversine)
     */
    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Centroïde géographique d'une collection de lieux
     *
     * @return array{latitude: float, longitude: float}|null
     */
    public static function centroidFromLieux(Collection $lieux): ?array
    {
        $valid = $lieux->filter(
            fn ($lieu) => $lieu->latitude !== null && $lieu->longitude !== null
        );

        if ($valid->isEmpty()) {
            return null;
        }

        return [
            'latitude' => (float) $valid->avg('latitude'),
            'longitude' => (float) $valid->avg('longitude'),
        ];
    }

    /**
     * Calcule le temps de trajet estimé en minutes entre deux points GPS
     * en fonction du mode de locomotion
     *
     * @param float $lat1 Latitude point de départ
     * @param float $lng1 Longitude point de départ
     * @param float $lat2 Latitude destination
     * @param float $lng2 Longitude destination
     * @param string $locomotion Mode de transport (pied, velo, trottinette, transports)
     * @return int Temps estimé en minutes
     */
    public static function calculerTempsTrajet(
        float $lat1, 
        float $lng1, 
        float $lat2, 
        float $lng2, 
        string $locomotion = 'pied'
    ): int {
        // Calcul de la distance
        $distanceKm = self::distanceKm($lat1, $lng1, $lat2, $lng2);
        
        // Vitesse moyenne selon le mode de locomotion (km/h)
        $vitesseMoyenne = match($locomotion) {
            'pied' => 5,           // Marche : 5 km/h
            'velo' => 15,          // Vélo : 15 km/h
            'trottinette' => 12,   // Trottinette électrique : 12 km/h
            'transports' => 25,    // Bus/Tram : 25 km/h (avec arrêts)
            default => 5
        };
        
        // Calcul du temps en minutes
        $tempsMinutes = ceil(($distanceKm / $vitesseMoyenne) * 60);
        
        // Ajout d'une marge de 2 minutes pour les imprévus
        $tempsMinutes += 2;
        
        return max(1, (int)$tempsMinutes); // Minimum 1 minute
    }

    /**
     * Formate le temps de trajet en texte lisible
     *
     * @param int $minutes Temps en minutes
     * @return string Texte formaté (ex: "15 min" ou "1h 30min")
     */
    public static function formaterTempsTrajet(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }
        
        $heures = floor($minutes / 60);
        $minsRestantes = $minutes % 60;
        
        if ($minsRestantes === 0) {
            return $heures . 'h';
        }
        
        return $heures . 'h ' . $minsRestantes . 'min';
    }
}
