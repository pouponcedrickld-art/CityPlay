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
}
