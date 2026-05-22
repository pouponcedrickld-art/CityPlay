<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgressionResource;
use App\Models\Partie;
use App\Services\GeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ProgressionApiController
 * 
 * Expose l'état de progression d'une partie au front.
 * Lecture seule pour le joueur.
 */
class ProgressionApiController extends Controller
{
    /**
     * Récupère la progression d'une partie
     * 
     * GET /api/parties/{partie}/progression
     * 
     * @param Partie $partie La partie concernée
     * @return JsonResponse Progression formatée
     */
    public function show(Partie $partie): JsonResponse
    {
        // Vérifie que l'utilisateur a accès à cette partie
        $this->authorize('view', $partie);

        // Charge les relations nécessaires pour la resource
        $progression = $partie->progression()
            ->with(['enigmeCourante.lieu'])
            ->first();

        if (!$progression) {
            return response()->json([
                'succes' => false,
                'message' => 'Aucune progression trouvée pour cette partie.',
            ], 404);
        }

        return response()->json([
            'succes' => true,
            'data' => new ProgressionResource($progression),
        ]);
    }

    /**
     * Calcule le temps de trajet estimé depuis la position du joueur jusqu'au lieu cible
     * 
     * POST /api/parties/{partie}/temps-trajet
     * Body: { lat: float, lng: float }
     * 
     * @param Request $request
     * @param Partie $partie
     * @return JsonResponse
     */
    public function calculerTempsTrajet(Request $request, Partie $partie): JsonResponse
    {
        $this->authorize('view', $partie);

        // Validation des coordonnées GPS du joueur
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        // Récupère la progression et l'énigme courante
        $progression = $partie->progression()->with('enigmeCourante.lieu')->first();

        if (!$progression || !$progression->enigmeCourante || !$progression->enigmeCourante->lieu) {
            return response()->json([
                'succes' => false,
                'message' => 'Aucune énigme active trouvée.',
            ], 404);
        }

        $lieu = $progression->enigmeCourante->lieu;

        // Vérifie que le lieu a des coordonnées valides
        if ($lieu->latitude === null || $lieu->longitude === null) {
            return response()->json([
                'succes' => false,
                'message' => 'Le lieu cible n\'a pas de coordonnées GPS.',
            ], 400);
        }

        // Récupère le mode de locomotion depuis les paramètres de la partie
        $parametres = $partie->parametres ?? [];
        $locomotion = $parametres['locomotion'] ?? 'pied';

        // Calcule le temps de trajet
        $tempsMinutes = GeoService::calculerTempsTrajet(
            $validated['lat'],
            $validated['lng'],
            (float) $lieu->latitude,
            (float) $lieu->longitude,
            $locomotion
        );

        // Formate le temps pour l'affichage
        $tempsFormate = GeoService::formaterTempsTrajet($tempsMinutes);

        // Calcule la distance
        $distanceKm = GeoService::distanceKm(
            $validated['lat'],
            $validated['lng'],
            (float) $lieu->latitude,
            (float) $lieu->longitude
        );

        return response()->json([
            'succes' => true,
            'data' => [
                'temps_minutes' => $tempsMinutes,
                'temps_formate' => $tempsFormate,
                'distance_km' => round($distanceKm, 2),
                'mode_locomotion' => $locomotion,
                'lieu_nom' => $lieu->nom,
            ],
            'message' => 'Temps de trajet calculé avec succès.',
        ]);
    }
}