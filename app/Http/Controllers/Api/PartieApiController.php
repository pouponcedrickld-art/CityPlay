<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePartieRequest;
use App\Models\Partie;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * PartieApiController (Jour 2)
 * 
 * Gère les parties en mode API REST (JSON).
 * Utilisé par le front pour les appels AJAX.
 */
class PartieApiController extends Controller
{
    protected InvitationService $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    /**
     * Liste les parties (JSON)
     */
    public function index(): JsonResponse
    {
        $parties = Partie::where('createur_id', auth()->id())
            ->orWhereHas('team.membres', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with('progression')
            ->get();

        return response()->json([
            'succes' => true,
            'data' => $parties,
        ]);
    }

    /**
     * Crée une partie (JSON)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'environnement_id' => 'required|exists:environnements,id',
            'mode' => 'required|in:solo,team',
            'parametres.duree' => 'required|integer|min:15|max:300',
            'parametres.locomotion' => 'required|in:pied,velo,voiture,moto',
            'parametres.difficulte' => 'required|integer|in:1,2,3',
            'parametres.nb_joueurs' => 'required|integer|min:1|max:9',
        ]);

        $partie = Partie::create([
            'createur_id' => auth()->id(),
            'environnement_id' => $validated['environnement_id'],
            'mode' => $validated['mode'],
            'parametres' => $validated['parametres'],
            'statut' => 'en_attente',
        ]);

        return response()->json([
            'succes' => true,
            'data' => $partie,
            'message' => 'Partie créée avec succès.',
        ], 201);
    }

    /**
     * Affiche une partie (JSON)
     */
    public function show(Partie $partie): JsonResponse
    {
        $this->authorize('view', $partie);

        return response()->json([
            'succes' => true,
            'data' => $partie->load('progression'),
        ]);
    }

    /**
     * Génère une invitation (code + lien)
     */
    public function genererInvitation(Partie $partie): JsonResponse
    {
        $this->authorize('genererInvitation', $partie);

        try {
            $ttlHours = config('game.ttl_hours', 24);
            $partie = $this->invitationService->genererInvitation($partie, $ttlHours);

            return response()->json([
                'succes' => true,
                'data' => [
                    'code_liaison' => $partie->code_liaison,
                    'lien_partage' => $partie->lien_partage,
                    'expires_at' => $partie->expires_at?->toIso8601String(),
                    'verrouillee' => $partie->verrouillee,
                ],
                'message' => 'Invitation générée avec succès.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'succes' => false,
                'message' => 'Erreur lors de la génération de l\'invitation.',
                'erreur' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Met à jour les paramètres
     */
    public function update(UpdatePartieRequest $request, Partie $partie): JsonResponse
    {
        $partie->update([
            'parametres' => array_merge(
                $partie->parametres ?? [],
                $request->validated()['parametres'] ?? []
            ),
        ]);

        return response()->json([
            'succes' => true,
            'data' => $partie->fresh(),
            'message' => 'Partie mise à jour avec succès.',
        ]);
    }

    /**
     * Rejoint une partie par code
     */
    public function rejoindreParCode(Request $request): JsonResponse
    {
        $request->validate([
            'code_liaison' => 'required|string|size:9',
        ]);

        $code = $request->input('code_liaison');
        $partie = $this->invitationService->trouverParCode($code);

        if (!$partie) {
            return response()->json([
                'succes' => false,
                'message' => 'Code invalide ou expiré.',
            ], 404);
        }

        $this->authorize('rejoindre', $partie);

        return response()->json([
            'succes' => true,
            'data' => [
                'partie_id' => $partie->id,
                'mode' => $partie->mode,
                'parametres' => $partie->parametres,
                'statut' => $partie->statut,
            ],
            'message' => 'Vous avez rejoint la partie avec succès.',
        ]);
    }

    /**
     * Supprime une partie
     */
    public function destroy(Partie $partie): JsonResponse
    {
        $this->authorize('delete', $partie);

        $partie->delete();

        return response()->json([
            'succes' => true,
            'message' => 'Partie supprimée avec succès.',
        ]);
    }
}