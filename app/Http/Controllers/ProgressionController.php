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

        // Si aucune énigme n'est définie (début de partie), on en choisit une (la plus difficile par défaut)
        if (!$progression->enigme_courante_id && !empty($progression->lieux_restants)) {
            $lieuId = $progression->lieux_restants[0];
            $enigme = Enigme::where('lieu_id', $lieuId)
                ->where('actif', true)
                ->orderByRaw("FIELD(type, 'force3', 'force2', 'force1', 'enfant')")
                ->first();

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
            'reponse'   => 'nullable|string',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $progression = Progression::where('partie_id', $partie->id)
            ->where('user_id', auth()->id())
            ->with('enigmeCourante.lieu')
            ->firstOrFail();

        $enigme = $progression->enigmeCourante;
        $isCorrect = false;

        // 1. Vérification par localisation (Prioritaire si fournie)
        if ($request->latitude && $request->longitude && $enigme->lieu->latitude && $enigme->lieu->longitude) {
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $enigme->lieu->latitude,
                $enigme->lieu->longitude
            );

            // Seuil de 10m (diamètre) -> 5m de rayon, ou 10m de tolérance selon l'interprétation. 
            // L'utilisateur dit "un cercle de 10m de diametre", donc 5m de rayon.
            if ($distance <= 10) {
                $isCorrect = true;
            }
        }

        // 2. Vérification par texte (si pas déjà validé par loc)
        if (!$isCorrect && $request->reponse && $enigme->reponse) {
            if (strtolower(trim($request->reponse)) === strtolower(trim($enigme->reponse))) {
                $isCorrect = true;
            }
        }

        if ($isCorrect) {
            // Bonne réponse : On passe au lieu suivant
            return $this->nextEnigme($partie);
        }

        // Mauvaise réponse : On descend en difficulté
        return $this->handleFailure($progression);
    }

    /**
     * Gère l'échec d'une énigme en proposant une version plus facile.
     */
    private function handleFailure(Progression $progression)
    {
        $currentEnigme = $progression->enigmeCourante;

        // Ordre des forces du plus dur au plus facile
        $forces = ['force3', 'force2', 'force1', 'enfant'];
        $currentIndex = array_search($currentEnigme->type, $forces);

        if ($currentIndex !== false && $currentIndex < count($forces) - 1) {
            // On cherche l'énigme suivante (plus facile) pour le même lieu
            $nextForce = $forces[$currentIndex + 1];
            $easierEnigme = Enigme::where('lieu_id', $currentEnigme->lieu_id)
                ->where('type', $nextForce)
                ->where('actif', true)
                ->first();

            if ($easierEnigme) {
                $progression->update(['enigme_courante_id' => $easierEnigme->id]);
                return back()->with('error', 'Mauvaise réponse. L\'énigme est maintenant plus facile !');
            }
        }

        return back()->with('error', 'Mauvaise réponse. Essayez encore ou demandez la solution.');
    }

    /**
     * Calcule la distance entre deux points GPS (Haversine).
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // mètres

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
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
                $nextEnigme = Enigme::where('lieu_id', $nextLieuId)
                    ->where('actif', true)
                    ->orderByRaw("FIELD(type, 'force3', 'force2', 'force1', 'enfant')")
                    ->first();
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
