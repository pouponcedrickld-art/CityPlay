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
     * Cette méthode récupère la progression du joueur et définit l'énigme active.
     */
    public function getCurrentEnigme(Partie $partie)
    {
        // On récupère la progression avec l'énigme actuelle et les données du lieu associé
        $progression = Progression::where('partie_id', $partie->id)
            ->where('user_id', auth()->id())
            ->with(['enigmeCourante.lieu.photos'])
            ->firstOrFail();

        // Si aucune énigme n'est définie (début de partie ou nouveau lieu), on en choisit une.
        // Par défaut, on prend l'énigme la plus difficile (force3 > force2 > force1 > enfant).
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
     * Soumet une réponse à l'énigme (soit par GPS, soit par texte).
     */
    public function submitAnswer(Request $request, Partie $partie)
    {
        // Validation des données entrantes (localisation optionnelle ou réponse texte)
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

        // 1. VÉRIFICATION PAR LOCALISATION (Prioritaire)
        // On compare la position GPS envoyée par le joueur avec celle définie pour le LIEU de l'énigme.
        if ($request->latitude && $request->longitude && $enigme->lieu->latitude && $enigme->lieu->longitude) {
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $enigme->lieu->latitude,
                $enigme->lieu->longitude
            );

            // Si le joueur est dans un rayon de 10 mètres, on valide.
            if ($distance <= 10) {
                $isCorrect = true;
            }
        }

        // 2. VÉRIFICATION PAR TEXTE (Si la localisation n'a pas validé l'énigme)
        // On compare la réponse saisie avec la réponse attendue en base.
        if (!$isCorrect && $request->reponse && $enigme->reponse) {
            if (strtolower(trim($request->reponse)) === strtolower(trim($enigme->reponse))) {
                $isCorrect = true;
            }
        }

        // RÉSULTAT DE LA VÉRIFICATION
        if ($isCorrect) {
            // Succès : Le joueur a trouvé ou est au bon endroit -> On passe au lieu suivant.
            return $this->nextEnigme($partie);
        }

        // Échec : Le joueur s'est trompé ou n'est pas assez proche -> On réduit la difficulté.
        return $this->handleFailure($progression);
    }

    /**
     * Gère l'échec d'une énigme.
     * Si possible, propose la même énigme mais à un niveau de difficulté inférieur.
     */
    private function handleFailure(Progression $progression)
    {
        $currentEnigme = $progression->enigmeCourante;

        // Ordre des difficultés : du plus complexe au plus simple.
        $forces = ['force3', 'force2', 'force1', 'enfant'];
        $currentIndex = array_search($currentEnigme->type, $forces);

        // Si on n'est pas déjà au niveau le plus simple ('enfant'), on descend d'un cran.
        if ($currentIndex !== false && $currentIndex < count($forces) - 1) {
            $nextForce = $forces[$currentIndex + 1];
            $easierEnigme = Enigme::where('lieu_id', $currentEnigme->lieu_id)
                ->where('type', $nextForce)
                ->where('actif', true)
                ->first();

            // Si une version plus simple existe pour ce lieu, on l'active.
            if ($easierEnigme) {
                $progression->update(['enigme_courante_id' => $easierEnigme->id]);
                return back()->with('error', 'Mauvaise réponse. L\'énigme est maintenant plus facile !');
            }
        }

        // Si aucune version plus simple n'est disponible ou si on est déjà au minimum.
        return back()->with('error', 'Mauvaise réponse. Essayez encore ou demandez la solution.');
    }

    /**
     * Calcule la distance en mètres entre deux coordonnées GPS (Formule de Haversine).
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Rayon de la Terre en mètres

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Affiche la page de succès après une bonne réponse.
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
     * Affiche la page d'échec (optionnel si on utilise le retour en arrière avec erreur).
     */
    public function showFailure(Partie $partie)
    {
        return Inertia::render('Player/Failure', [
            'partie' => $partie
        ]);
    }

    /**
     * Marque le lieu actuel comme découvert et passe au lieu suivant de la liste.
     */
    public function nextEnigme(Partie $partie)
    {
        $progression = Progression::where('partie_id', $partie->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $lieuxRestants = $progression->lieux_restants;
        $lieuxDecouverts = $progression->lieux_decouverts ?? [];

        if (!empty($lieuxRestants)) {
            // On déplace le lieu actuel des 'restants' vers les 'découverts'
            $currentLieuId = array_shift($lieuxRestants);
            $lieuxDecouverts[] = $currentLieuId;

            $nextEnigmeId = null;
            // Si il reste des lieux à visiter, on pré-charge l'énigme du prochain lieu (la plus dure).
            if (!empty($lieuxRestants)) {
                $nextLieuId = $lieuxRestants[0];
                $nextEnigme = Enigme::where('lieu_id', $nextLieuId)
                    ->where('actif', true)
                    ->orderByRaw("FIELD(type, 'force3', 'force2', 'force1', 'enfant')")
                    ->first();
                $nextEnigmeId = $nextEnigme ? $nextEnigme->id : null;
            }

            // Mise à jour de la progression globale
            $progression->update([
                'lieux_restants' => $lieuxRestants,
                'lieux_decouverts' => $lieuxDecouverts,
                'enigme_courante_id' => $nextEnigmeId,
                'nb_enigmes_resolues' => $progression->nb_enigmes_resolues + 1
            ]);
        }

        // Si plus aucun lieu ne reste, la partie est terminée.
        if (empty($progression->lieux_restants)) {
            return redirect()->route('progression.summary', $partie->id);
        }

        // Sinon on redirige vers l'énigme du nouveau lieu.
        return redirect()->route('progression.enigme', $partie->id);
    }

    /**
     * Affiche le résumé final de la partie une fois terminée.
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
