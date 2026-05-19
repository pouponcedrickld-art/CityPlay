<?php

namespace App\Http\Controllers;

use App\Models\Partie;
use App\Models\Enigme;
use App\Models\Lieu;
use App\Services\GameplayService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgressionController extends Controller
{
    protected GameplayService $gameplayService;

    public function __construct(GameplayService $gameplayService)
    {
        $this->gameplayService = $gameplayService;
    }

    public function getCurrentEnigme(Partie $partie)
    {
        try {
            $progression = $partie->progression;

            if (!$progression) {
                // Initialiser la partie si elle n'a pas encore de progression
                $progression = $this->gameplayService->initialiserPartie($partie);
            }

            if ($progression->estTerminee()) {
                return redirect()->route('progression.summary', $partie);
            }

            $enigme = Enigme::with('lieu.photos')->find($progression->enigme_courante_id);

            if (!$enigme) {
                // Si l'énigme n'existe plus ou n'est pas trouvée, on essaie de passer à la suivante
                if ($progression->passerEnigmeSuivante()) {
                    return redirect()->route('progression.enigme', $partie);
                }
                return redirect()->route('progression.summary', $partie);
            }

            return Inertia::render('Player/Enigme', [
                'partie' => $partie->load('environnement'),
                'enigme' => $enigme,
                'progression' => $progression,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', "Impossible de lancer la partie : " . $e->getMessage());
        }
    }

    /**
     * Gère la soumission d'une réponse (texte ou GPS)
     */
    public function submitAnswer(Request $request, Partie $partie)
    {
        $progression = $partie->progression;

        if (!$progression || $progression->estTerminee()) {
            return back()->with('error', 'Action impossible sur une partie terminée.');
        }

        // Cas 1 : Soumission GPS
        if ($request->has('latitude') && $request->has('longitude')) {
            $resultat = $this->gameplayService->validerGps(
                $partie,
                (float) $request->latitude,
                (float) $request->longitude,
                (float) $request->precision
            );

            if ($resultat['succes']) {
                $lieu = $progression->enigmeCourante->lieu;
                return redirect()->route('progression.success', ['partie' => $partie, 'lieu' => $lieu->id]);
            }

            return back()->with('error', $resultat['message']);
        }

        // Cas 2 : Soumission texte
        $request->validate(['reponse' => 'required|string']);
        $enigme = $progression->enigmeCourante;

        // Comparaison simple (ignorer casse et espaces)
        $reponseJoueur = trim(mb_strtolower($request->reponse));
        $reponseAttendue = trim(mb_strtolower($enigme->reponse));

        if ($reponseJoueur === $reponseAttendue) {
            $progression->resoudreEnigmeCourante();
            $aSuivante = $progression->passerEnigmeSuivante();
            
            $lieu = $enigme->lieu;
            return redirect()->route('progression.success', ['partie' => $partie, 'lieu' => $lieu->id]);
        }

        return redirect()->route('progression.failure', $partie);
    }

    public function showSuccess(Partie $partie, Request $request)
    {
        $lieu = Lieu::with('photos')->find($request->lieu);
        
        return Inertia::render('Player/Success', [
            'partie' => $partie->load('environnement'),
            'lieu' => $lieu
        ]);
    }

    public function showFailure(Partie $partie)
    {
        return Inertia::render('Player/Failure', [
            'partie' => $partie->load('environnement')
        ]);
    }

    public function nextEnigme(Partie $partie)
    {
        $progression = $partie->progression;
        
        if ($progression->estTerminee()) {
            return redirect()->route('progression.summary', $partie);
        }

        return redirect()->route('progression.enigme', $partie);
    }

    public function showSummary(Partie $partie)
    {
        return Inertia::render('Player/Summary', [
            'partie' => $partie->load('environnement', 'progression'),
        ]);
    }

    /**
     * Sauvegarde l'état de la progression (ex: temps restant)
     */
    public function store(Request $request, Partie $partie)
    {
        $progression = $partie->progression;
        if (!$progression) return response()->json(['error' => 'Pas de progression'], 404);

        if ($request->has('temps_restant')) {
            $progression->update(['temps_restant' => $request->temps_restant]);
        }

        return response()->json(['success' => true]);
    }
}
