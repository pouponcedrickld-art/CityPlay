<?php

namespace App\Http\Controllers;

use App\Models\Partie;
use App\Models\Progression;
use App\Models\Enigme;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgressionController extends Controller
{
    /**
     * Affiche l'énigme courante pour une partie donnée.
     */
    public function getCurrentEnigme(Partie $partie)
    {
        $progression = Progression::where('partie_id', $partie->id)
            ->where('user_id', auth()->id())
            ->with(['enigmeCourante.lieu.photos'])
            ->firstOrFail();

        // Si aucune énigme n'est définie (début de partie), on en choisit une
        if (!$progression->enigme_courante_id && !empty($progression->lieux_restants)) {
            $lieuId = $progression->lieux_restants[0];
            $enigme = Enigme::where('lieu_id', $lieuId)->where('actif', true)->first();

            if ($enigme) {
                $progression->update(['enigme_courante_id' => $enigme->id]);
                $progression->load('enigmeCourante.lieu.photos');
            }
        }

        return Inertia::render('Player/Enigme', [
            'partie' => $partie,
            'progression' => $progression,
            'enigme' => $progression->enigmeCourante
        ]);
    }

    /**
     * Soumet une réponse à l'énigme.
     */
    public function submitAnswer(Request $request, Partie $partie)
    {
        $request->validate([
            'reponse' => 'required|string',
        ]);

        $progression = Progression::where('partie_id', $partie->id)
            ->where('user_id', auth()->id())
            ->with('enigmeCourante')
            ->firstOrFail();

        // Logique de vérification (à adapter selon si on a un champ 'reponse' dans Enigme)
        // Pour l'exemple, on simule une réussite
        $isCorrect = true;

        if ($isCorrect) {
            return redirect()->route('progression.success', $partie->id);
        }

        return redirect()->route('progression.failure', $partie->id);
    }

    /**
     * Page de succès après une bonne réponse.
     */
    public function showSuccess(Partie $partie)
    {
        $progression = Progression::where('partie_id', $partie->id)
            ->where('user_id', auth()->id())
            ->with(['enigmeCourante.lieu.photos'])
            ->firstOrFail();

        return Inertia::render('Player/Success', [
            'partie' => $partie,
            'lieu' => $progression->enigmeCourante->lieu
        ]);
    }

    /**
     * Page d'échec après une mauvaise réponse.
     */
    public function showFailure(Partie $partie)
    {
        return Inertia::render('Player/Failure', [
            'partie' => $partie
        ]);
    }

    /**
     * Passe à l'énigme suivante.
     */
    public function nextEnigme(Partie $partie)
    {
        $progression = Progression::where('partie_id', $partie->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $lieuxRestants = $progression->lieux_restants;
        $lieuxDecouverts = $progression->lieux_decouverts ?? [];

        if (!empty($lieuxRestants)) {
            $currentLieuId = array_shift($lieuxRestants);
            $lieuxDecouverts[] = $currentLieuId;

            $nextEnigmeId = null;
            if (!empty($lieuxRestants)) {
                $nextLieuId = $lieuxRestants[0];
                $nextEnigme = Enigme::where('lieu_id', $nextLieuId)->where('actif', true)->first();
                $nextEnigmeId = $nextEnigme ? $nextEnigme->id : null;
            }

            $progression->update([
                'lieux_restants' => $lieuxRestants,
                'lieux_decouverts' => $lieuxDecouverts,
                'enigme_courante_id' => $nextEnigmeId,
                'nb_enigmes_resolues' => $progression->nb_enigmes_resolues + 1
            ]);
        }

        if (empty($progression->lieux_restants)) {
            return redirect()->route('progression.summary', $partie->id);
        }

        return redirect()->route('progression.enigme', $partie->id);
    }

    /**
     * Affiche le résumé de fin de partie.
     */
    public function showSummary(Partie $partie)
    {
        $progression = Progression::where('partie_id', $partie->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Player/Summary', [
            'partie' => $partie,
            'progression' => $progression
        ]);
    }
}
