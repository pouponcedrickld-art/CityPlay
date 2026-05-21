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
            // Sécurité : Une partie terminée ne doit plus être jouée
            if ($partie->statut === 'terminee' || ($partie->progression && $partie->progression->estTerminee())) {
                return redirect()->route('progression.summary', $partie)
                    ->with('info', 'Cette partie est déjà terminée.');
            }

            $progression = $partie->progression;

            if (!$progression) {
                $progression = $this->gameplayService->initialiserPartie($partie);
            }

            if ($progression->estTerminee()) {
                return redirect()->route('progression.summary', $partie);
            }

            $enigme = Enigme::with('lieu')->find($progression->enigme_courante_id);

            if (!$enigme) {
                if ($progression->passerEnigmeSuivante()) {
                    return redirect()->route('progression.enigme', $partie);
                }
                return redirect()->route('progression.summary', $partie);
            }

            return Inertia::render('Player/Enigme', [
                'partie' => $partie->load('environnement'),
                'enigme' => $this->enigmePourJoueur($enigme),
                'progression' => $progression->fresh(),
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
            \Log::info('ProgressionController: Soumission GPS reçue', [
                'partie_id' => $partie->id,
                'lat' => $request->latitude,
                'lng' => $request->longitude,
                'precision' => $request->precision
            ]);

            $precision = $request->filled('precision')
                ? (float) $request->input('precision')
                : null;

            $resultat = $this->gameplayService->validerGps(
                $partie,
                (float) $request->latitude,
                (float) $request->longitude,
                $precision
            );

            if ($resultat['succes']) {
                return redirect()->route('progression.success', [
                    'partie' => $partie,
                    'lieu' => $resultat['lieu_id'],
                ])->with([
                    'points_gagnes' => $resultat['points_gagnes'] ?? 0,
                    'score_total' => $resultat['score_total'] ?? 0,
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
            $pointsGagnes = $progression->resoudreEnigmeCourante();
            $progression->refresh();
            $progression->passerEnigmeSuivante();

            return redirect()->route('progression.success', [
                'partie' => $partie,
                'lieu' => $lieuId,
            ])->with([
                'points_gagnes' => $pointsGagnes,
                'score_total' => $progression->score,
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

        $resultat = $this->gameplayService->passerEnigme($partie);

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
