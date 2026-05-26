<?php

namespace App\Http\Controllers;

use App\Events\EnigmeResolue;
use App\Models\Partie;
use App\Models\Enigme;
use App\Models\Lieu;
use App\Services\GameplayService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Contrôleur gérant le déroulement actif d'une partie.
 *
 * S'occupe de l'affichage de l'énigme actuelle, de la validation des réponses
 * (texte ou GPS) et du passage d'un lieu à un autre.
 */
class ProgressionController extends Controller
{
    protected GameplayService $gameplayService;

    /**
     * Injection du GameplayService qui contient la logique de validation complexe.
     */
    public function __construct(GameplayService $gameplayService)
    {
        $this->gameplayService = $gameplayService;
    }

    /**
     * Affiche l'énigme actuelle pour une partie donnée.
     *
     * Gère l'initialisation de la progression si c'est le début de la partie.
     */
    public function getCurrentEnigme(Request $request, Partie $partie)
    {
        try {
            // 1. Sécurité : Une partie terminée ne doit plus être jouée
            if ($partie->statut === 'terminee' || ($partie->progression && $partie->progression->estTerminee())) {
                return redirect()->route('progression.summary', $partie)
                    ->with('info', 'Cette partie est déjà terminée.');
            }

            $progression = $partie->progression;

            // 2. Initialisation si première visite
            if (!$progression) {
                $progression = $this->gameplayService->initialiserPartie(
                    $partie,
                    $partie->lat_depart ? (float) $partie->lat_depart : ($request->latitude ? (float) $request->latitude : null),
                    $partie->lng_depart ? (float) $partie->lng_depart : ($request->longitude ? (float) $request->longitude : null)
                );
            }

            // 3. Vérification si la progression vient d'être terminée (après init ou skip)
            if ($progression->estTerminee()) {
                return redirect()->route('progression.summary', $partie);
            }

            // 4. Récupération de l'énigme courante avec son lieu
            $enigme = Enigme::with('lieu')->find($progression->enigme_courante_id);

            // 5. Fallback si l'énigme est introuvable (tentative de passer à la suivante)
            if (!$enigme) {
                if ($progression->passerEnigmeSuivante()) {
                    return redirect()->route('progression.enigme', $partie);
                }
                return redirect()->route('progression.summary', $partie);
            }

            // 6. Rendu de la vue de jeu avec les données nécessaires
            return Inertia::render('Player/Enigme', [
                'partie' => $partie->load(['environnement', 'team.users']),
                'enigme' => $this->enigmePourJoueur($enigme), // On filtre les infos sensibles (réponse)
                'progression' => $progression->fresh(),
            ]);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', "Impossible de lancer la partie : " . $e->getMessage());
        }
    }

    /**
     * Gère la soumission d'une réponse par le joueur (texte ou validation GPS).
     */
    public function submitAnswer(Request $request, Partie $partie)
    {
        $progression = $partie->progression;

        if (!$progression || $progression->estTerminee()) {
            return back()->with('error', 'Action impossible sur une partie terminée.');
        }

        // --- CAS 1 : Validation par Géolocalisation (Arrivée sur un lieu) ---
        if ($request->has('latitude') && $request->has('longitude')) {
            $enigmeIdResolue = $progression->enigme_courante_id;

            \Log::info('ProgressionController: Soumission GPS reçue', [
                'partie_id' => $partie->id,
                'lat' => $request->latitude,
                'lng' => $request->longitude,
                'precision' => $request->precision
            ]);

            $precision = $request->filled('precision')
                ? (float) $request->input('precision')
                : null;

            // Appel au service pour vérifier si les coordonnées correspondent au lieu actuel
            $resultat = $this->gameplayService->validerGps(
                $partie,
                (float) $request->latitude,
                (float) $request->longitude,
                $precision
            );

            if ($resultat['succes']) {
                // Stockage temporaire des points gagnés pour affichage dans la vue de succès
                session()->flash('points_gagnes', $resultat['points_gagnes'] ?? 0);
                session()->flash('score_total', $resultat['score_total'] ?? 0);

                // Déclenche l'événement WebSocket pour notifier tous les membres de l'équipe
                broadcast(new EnigmeResolue(
                    $partie,
                    auth()->user(),
                    $enigmeIdResolue,
                    $partie->fresh()->progression->enigme_courante_id,
                    auth()->user()->name . ' a validé la position GPS !',
                    $resultat['lieu_id']
                ))->toOthers();

                return redirect()->route('progression.success', [
                    'partie' => $partie,
                    'lieu' => $resultat['lieu_id'],
                ]);
            }

            return back()->with([
                'error' => $resultat['message'],
                'gps_validation' => [
                    'distance' => $resultat['distance'] ?? null,
                    'precision' => $resultat['precision'] ?? $precision,
                    'erreur' => $resultat['erreur'] ?? null,
                ],
            ]);
        }

        // Cas 2 : Soumission texte
        $request->validate(['reponse' => 'required|string']);
        $enigme = $progression->enigmeCourante;

        if (!$enigme) {
            return back()->with('error', 'Aucune énigme en cours.');
        }

        $reponseJoueur = trim(mb_strtolower($request->reponse));
        $reponseAttendue = trim(mb_strtolower($enigme->reponse));

        if ($reponseJoueur === $reponseAttendue) {
            $lieuId = $enigme->lieu_id;
            $enigmeId = $enigme->id;
            $pointsGagnes = $progression->resoudreEnigmeCourante();
            $progression->refresh();

            session()->flash('points_gagnes', $pointsGagnes);
            session()->flash('score_total', $progression->score);

            $progression->passerEnigmeSuivante();

            // Déclenche l'événement WebSocket pour notifier tous les membres de l'équipe
            broadcast(new EnigmeResolue(
                $partie,
                auth()->user(),
                $enigmeId,
                $progression->enigme_courante_id,
                auth()->user()->name . ' a résolu l\'énigme ! Passage à la suivante...',
                $lieuId
            ))->toOthers();

            return redirect()->route('progression.success', [
                'partie' => $partie,
                'lieu' => $lieuId,
            ]);
        }

        return redirect()->route('progression.failure', $partie);
    }

    public function showSuccess(Partie $partie, Request $request)
    {
        $lieu = Lieu::find($request->lieu);

        // On récupère les photos avec la logique robuste (table ou JSON legacy)
        $photos = $lieu ? $this->lieuPhotosPourJoueur($lieu) : [];

        return Inertia::render('Player/Success', [
            'partie' => $partie->load('environnement'),
            'lieu' => $lieu ? array_merge($lieu->toArray(), ['photos' => $photos]) : null,
            'progression' => $partie->progression,
            'points_gagnes' => (int) session('points_gagnes', 0),
            'score_total' => (int) session('score_total', $partie->progression?->score ?? 0),
        ]);
    }

    public function showFailure(Partie $partie)
    {
        return Inertia::render('Player/Failure', [
            'partie' => $partie->load('environnement'),
            'progression' => $partie->progression,
        ]);
    }

    /**
     * Affiche la solution de l'énigme courante (sans avancer la progression).
     */
    public function showSolution(Partie $partie)
    {
        $resultat = $this->gameplayService->solutionEnigmeCourante($partie);

        if (!$resultat['succes']) {
            return back()->with('error', $resultat['message']);
        }

        return back()->with('solution_revelee', $resultat['solution']);
    }

    /**
     * Passer l'énigme courante sans gagner de points (bouton Passer / solution).
     */
    public function nextEnigme(Partie $partie)
    {
        $progression = $partie->progression;

        if (!$progression || $progression->estTerminee()) {
            return redirect()->route('progression.summary', $partie);
        }

        $enigmeId = $progression->enigme_courante_id;
        $resultat = $this->gameplayService->passerEnigme($partie);

        // Déclenche l'événement WebSocket pour notifier tous les membres de l'équipe
        broadcast(new EnigmeResolue(
            $partie,
            auth()->user(),
            $enigmeId,
            $partie->fresh()->progression->enigme_courante_id,
            auth()->user()->name . ' a passé l\'énigme.'
        ))->toOthers();

        if ($resultat['partie_terminee'] ?? false) {
            return redirect()->route('progression.summary', $partie);
        }

        return redirect()->route('progression.enigme', $partie);
    }

    public function showSummary(Partie $partie)
    {
        $partie->load('environnement', 'progression');

        if ($partie->progression && $partie->score_total !== $partie->progression->score) {
            $partie->update(['score_total' => $partie->progression->score]);
        }

        return Inertia::render('Player/Summary', [
            'partie' => $partie,
        ]);
    }

    public function showCarte(Partie $partie)
    {
        return Inertia::render('Player/Carte', [
            'partie' => $partie->load('environnement'),
            'progression' => $partie->progression,
        ]);
    }

    /**
     * Sauvegarde l'état de la progression (ex: temps restant)
     */
    public function store(Request $request, Partie $partie)
    {
        $progression = $partie->progression;
        if (!$progression) {
            return $request->header('X-Inertia')
                ? back()->with('error', 'Pas de progression')
                : response()->json(['error' => 'Pas de progression'], 404);
        }

        if ($request->has('temps_restant')) {
            $progression->update(['temps_restant' => $request->temps_restant]);
        }

        return $request->header('X-Inertia')
            ? back()
            : response()->json(['success' => true]);
    }

    /**
     * Données énigme exposées au joueur (sans nom ni coordonnées du lieu).
     */
    private function enigmePourJoueur(Enigme $enigme): array
    {
        $lieu = $enigme->lieu;
        $gpsDisponible = $lieu
            && $lieu->latitude !== null
            && $lieu->longitude !== null;

        return [
            'id' => $enigme->id,
            'type' => $enigme->type,
            'titre' => $enigme->titre,
            'texte' => $enigme->texte,
            'indice' => $enigme->indice,
            'points' => $enigme->points,
            'image_url' => $enigme->full_image_url,
            'lieu' => [
                'gps_disponible' => $gpsDisponible,
                'photos' => $this->lieuPhotosPourJoueur($lieu),
            ],
        ];
    }

    /**
     * Photos du lieu pour le joueur (relation photo_lieus ou colonne JSON legacy).
     *
     * @return array<int, array{url: string}>
     */
    private function lieuPhotosPourJoueur(?Lieu $lieu): array
    {
        if (!$lieu) {
            return [];
        }

        $fromTable = $lieu->photos()
            ->orderBy('ordre')
            ->get()
            ->map(fn ($photo) => ['url' => $photo->full_url])
            ->all();

        if ($fromTable !== []) {
            return $fromTable;
        }

        $legacy = $lieu->getAttributes()['photos'] ?? null;
        if ($legacy === null) {
            return [];
        }

        $decoded = is_string($legacy) ? json_decode($legacy, true) : $legacy;
        if (!is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->map(function ($item) {
                $path = is_string($item) ? $item : ($item['url'] ?? '');
                if (!$path) return null;

                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    return ['url' => $path];
                }
                return ['url' => asset('storage/' . $path)];
            })
            ->filter()
            ->values()
            ->all();
    }
}
