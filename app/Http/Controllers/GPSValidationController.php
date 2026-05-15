<?php

namespace App\Http\Controllers;

use App\Helpers\GeoHelper;
use App\Models\Lieu;
use Illuminate\Http\Request;

class GPSValidationController extends Controller
{
    public function validatePosition(Request $request, Lieu $lieu)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'precision' => 'nullable|numeric', // précision GPS en mètres (optionnel)
        ]);

        // Vérification précision GPS trop faible
        if ($request->precision && $request->precision > 50) {
            return response()->json([
                'valide' => false,
                'message' => 'Précision GPS insuffisante. Réessayez dans un endroit plus dégagé.',
                'precision' => $request->precision,
            ], 422);
        }

        $distance = GeoHelper::haversineDistance(
            $request->latitude,
            $request->longitude,
            $lieu->latitude,
            $lieu->longitude
        );

        $valide = GeoHelper::estDansLeRayon(
            $request->latitude,
            $request->longitude,
            $lieu->latitude,
            $lieu->longitude,
            $lieu->rayon_metres
        );

        return response()->json([
            'valide' => $valide,
            'distance' => round($distance, 2),
            'rayon' => $lieu->rayon_metres,
            'message' => $valide
                ? 'Vous êtes bien sur le lieu !'
                : "Vous n'êtes pas encore sur le lieu. ({$distance} m)",
        ]);
    }
}