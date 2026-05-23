<?php

namespace App\Http\Controllers\Api;

use App\Events\EnigmeResolue;
use App\Http\Controllers\Controller;
use App\Models\Partie;
use App\Services\GameplayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GameplayApiController
 * 
 * Gère toutes les actions du joueur pendant une partie :
 * - Valider GPS
 * - Passer énigme
 * - Demander indice
 * - Révéler solution
 * - Pause / Reprendre / Terminer
 */
class GameplayApiController extends Controller
{
    protected GameplayService $gameplayService;

    public function __construct(GameplayService $gameplayService)
    {
        $this->gameplayService = $gameplayService;
    }

    /**
     * Initialise une nouvelle partie (démarre le jeu)
     * 
     * POST /api/parties/{partie}/demarrer
     */
    public function demarrer(Partie $partie): JsonResponse
    {
        $this->authorize('view', $partie);

        try {
            $progression = $this->gameplayService->initialiserPartie($partie);

            return response()->json([
                'succes' => true,
                'data' => [
                    'progression_id' => $progression->id,
                    'enigme_courante' => $progression->enigmeCourante,
                    'temps_restant' => $progression->temps_restant,
                ],
                'message' => 'Partie démarrée avec succès !',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'succes' => false,
                'message' => 'Erreur lors du démarrage.',
                'erreur' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Valide la position GPS du joueur
     * 
     * POST /api/parties/{partie}/valider-gps
     * Body : { lat: float, lng: float, precision?: float }
     */
    public function validerGps(Request $request, Partie $partie): JsonResponse
    {
        $this->authorize('view', $partie);

        // Validation des coordonnées GPS
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'precision' => 'nullable|numeric|min:0',
        ]);

        $resultat = $this->gameplayService->validerGps(
            $partie,
            $validated['lat'],
            $validated['lng'],
            $validated['precision'] ?? null
        );

        $statusCode = $resultat['succes'] ? 200 : 400;

        return response()->json([
            'succes' => $resultat['succes'],
            'data' => $resultat,
            'message' => $resultat['message'],
        ], $statusCode);
    }

    /**
     * Passe l'énigme courante (sans la résoudre)
     * 
     * POST /api/parties/{partie}/passer-enigme
     */
    public function passerEnigme(Partie $partie): JsonResponse
    {
        $this->authorize('view', $partie);

        $resultat = $this->gameplayService->passerEnigme($partie);

        return response()->json([
            'succes' => $resultat['succes'],
            'data' => $resultat,
            'message' => $resultat['message'],
        ]);
    }

    /**
     * Demande un indice pour l'énigme courante
     * 
     * POST /api/parties/{partie}/indice
     */
    public function demanderIndice(Partie $partie): JsonResponse
    {
        $this->authorize('view', $partie);

        $resultat = $this->gameplayService->demanderIndice($partie);

        return response()->json([
            'succes' => $resultat['succes'],
            'data' => $resultat,
            'message' => $resultat['message'] ?? 'Indice fourni.',
        ]);
    }

    /**
     * Révèle la solution de l'énigme courante
     * 
     * POST /api/parties/{partie}/solution
     */
    public function revelerSolution(Partie $partie): JsonResponse
    {
        $this->authorize('view', $partie);

        $resultat = $this->gameplayService->revelerSolution($partie);

        return response()->json([
            'succes' => $resultat['succes'],
            'data' => $resultat,
            'message' => $resultat['message'],
        ]);
    }

    /**
     * Met la partie en pause
     * 
     * POST /api/parties/{partie}/pause
     */
    public function mettreEnPause(Partie $partie): JsonResponse
    {
        $this->authorize('view', $partie);

        $this->gameplayService->mettreEnPause($partie);

        return response()->json([
            'succes' => true,
            'message' => 'Partie mise en pause.',
        ]);
    }

    /**
     * Reprend une partie en pause
     * 
     * POST /api/parties/{partie}/reprendre
     */
    public function reprendre(Partie $partie): JsonResponse
    {
        $this->authorize('view', $partie);

        $this->gameplayService->reprendre($partie);

        return response()->json([
            'succes' => true,
            'message' => 'Partie reprise.',
        ]);
    }

    /**
     * Termine la partie (abandon ou fin)
     * 
     * POST /api/parties/{partie}/terminer
     */
    public function terminer(Partie $partie): JsonResponse
    {
        $this->authorize('view', $partie);

        $this->gameplayService->terminerPartie($partie);

        return response()->json([
            'succes' => true,
            'message' => 'Partie terminée.',
        ]);
    }
}