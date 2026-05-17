<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgressionResource;
use App\Models\Partie;
use Illuminate\Http\JsonResponse;

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
}