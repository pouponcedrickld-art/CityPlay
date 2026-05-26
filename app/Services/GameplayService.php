<?php

namespace App\Services;

use App\Models\Partie;
use App\Models\ProgressionPartie;
use App\Models\Enigme;
use App\Models\Lieu;
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion du gameplay
 *
 * Responsable de :
 * - Initialiser la progression d'une nouvelle partie
 * - Gérer les actions du joueur (valider, passer, indice, solution)
 * - Calculer le nombre d'énigmes selon durée et locomotion
 * - Sélectionner les lieux selon classement mairie
 */
class GameplayService
{
    /**
     * Service de validation GPS (injection de dépendance)
     */
    protected GpsValidationService $gpsService;

    public function __construct(GpsValidationService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    /**
     * Initialise une nouvelle partie
     * - Calcule le nombre d'énigmes
     * - Sélectionne les lieux
     * - Crée la progression
     *
     * @param Partie $partie La partie à initialiser
     * @param float|null $lat Latitude de départ
     * @param float|null $lng Longitude de départ
     * @return ProgressionPartie La progression créée
     */
    public function initialiserPartie(Partie $partie, ?float $lat = null, ?float $lng = null): ProgressionPartie
    {
        \Log::info('GameplayService: Début initialisation progression', [
            'partie_id' => $partie->id,
            'lat' => $lat,
            'lng' => $lng
        ]);

        // Récupère les paramètres de la partie
        $parametres = $partie->parametres;
        $dureeMinutes = (int)($parametres['duree'] ?? 60);
        $locomotion = $parametres['locomotion'] ?? 'pied';
        $difficulte = (int)($parametres['difficulte'] ?? 2);

        \Log::info('GameplayService: Paramètres extraits', [
            'duree' => $dureeMinutes,
            'locomotion' => $locomotion,
            'difficulte' => $difficulte,
            'ordre_jeu' => $partie->ordre_jeu
        ]);

        // === ÉTAPE 1 : Calcul du nombre d'énigmes ===
        $nbEnigmes = $this->calculerNombreEnigmes($dureeMinutes, $locomotion);
        \Log::info('GameplayService: Nombre d\'énigmes calculé', ['count' => $nbEnigmes]);

        // === ÉTAPE 2 : Sélection des lieux ===
        $user = \App\Models\User::find($partie->createur_id);
        $lieux = $this->selectionnerLieux($partie->environnement_id, $nbEnigmes, $difficulte, $user);

        // --- TRI PAR PROXIMITÉ (Point 6) ---
        if ($partie->ordre_jeu === 'proximite' && $lat !== null && $lng !== null) {
            \Log::info('GameplayService: Tri des lieux par proximité');
            $lieux = $lieux->sortBy(function ($lieu) use ($lat, $lng) {
                return $this->gpsService->calculerDistance($lat, $lng, $lieu->latitude, $lieu->longitude);
            })->values();
        }

        \Log::info('GameplayService: Lieux sélectionnés', ['count' => $lieux->count(), 'ids' => $lieux->pluck('id')]);

        if ($lieux->isEmpty()) {
            \Log::error('GameplayService: Aucun lieu trouvé pour cet environnement');
            throw new \Exception("Aucun lieu avec énigme n'a été trouvé pour cet environnement.");
        }

        // === ÉTAPE 3 : Déterminer l'énigme de départ ===
        $premierLieu = $lieux->first();
        $typeEnigme = match ($difficulte) {
            1 => 'force1',
            2 => 'force2',
            3 => 'force3',
            default => 'force2',
        };

        $enigmeDepart = $premierLieu->enigmes()->where('type', $typeEnigme)->where('actif', true)->first()
                        ?? $premierLieu->enigmes()->where('actif', true)->first();

        if (!$enigmeDepart) {
            \Log::error('GameplayService: Aucune énigme active trouvée pour le premier lieu', ['lieu_id' => $premierLieu->id]);
            throw new \Exception("Le premier lieu de ce parcours ne contient aucune énigme active.");
        }

        \Log::info('GameplayService: Énigme de départ identifiée', ['id' => $enigmeDepart->id]);

        // === ÉTAPE 4 : Création de la progression ===
        $lieuxIds = $lieux->pluck('id')->toArray();
        $lieuxRestants = $lieuxIds;
        array_shift($lieuxRestants); // Le premier est déjà en cours

        $progression = ProgressionPartie::create([
            'partie_id' => $partie->id,
            'lieux_a_visiter' => $lieuxIds,
            'lieux_decouverts' => [],
            'lieux_restants' => $lieuxRestants,
            'enigme_courante_id' => $enigmeDepart->id,
            'temps_restant' => $dureeMinutes * 60,
            'score' => 0,
            'statut' => 'en_cours',
        ]);

        \Log::info('GameplayService: Progression créée', ['id' => $progression->id]);

        // Démarre la partie
        $partie->update([
            'statut' => 'en_cours',
            'started_at' => now(),
        ]);

        \Log::info('GameplayService: Partie marquée comme en cours');

        return $progression;
    }

    /**
     * Sélectionne les lieux pour une partie
     * Exclut les lieux déjà découverts par le joueur si possible
     */
    private function selectionnerLieux(int $environnementId, int $nbEnigmes, int $difficulte, ?\App\Models\User $user = null)
    {
        $lieuxDecouvertsIds = $user ? $user->getLieuxDecouvertsIds() : [];
        \Log::info('GameplayService: Filtrage des lieux déjà découverts', ['ids' => $lieuxDecouvertsIds]);

        // On cherche d'abord les lieux qui ont l'énigme de la difficulté demandée
        $typeEnigme = match ($difficulte) {
            1 => 'force1',
            2 => 'force2',
            3 => 'force3',
            default => 'force2',
        };

        $query = Lieu::where('environnement_id', $environnementId)
            ->where('actif', true)
            ->whereHas('enigmes', function ($query) use ($typeEnigme) {
                $query->where('type', $typeEnigme)->where('actif', true);
            })
            ->whereNotIn('id', $lieuxDecouvertsIds) // Exclure les déjà trouvés
            ->orderBy('ordre', 'asc')
            ->limit($nbEnigmes);

        $lieux = $query->get();

        // Si on n'en a pas assez, on complète avec n'importe quel lieu (actif) jamais visité par le joueur
        if ($lieux->count() < $nbEnigmes) {
            $idsExistants = $lieux->pluck('id')->toArray();

            $lieuxComplementaires = Lieu::where('environnement_id', $environnementId)
                ->where('actif', true)
                ->whereNotIn('id', array_merge($idsExistants, $lieuxDecouvertsIds))
                ->whereHas('enigmes', function ($query) {
                    $query->where('actif', true);
                })
                ->orderBy('ordre', 'asc')
                ->limit($nbEnigmes - $lieux->count())
                ->get();

            $lieux = $lieux->concat($lieuxComplementaires)->sortBy('ordre');
        }

        // Cas critique : Si après filtrage on n'a absolument AUCUN lieu jamais visité
        // On autorise à rejouer les anciens lieux (sinon le joueur est bloqué à vie sur cet environnement)
        if ($lieux->isEmpty()) {
            \Log::warning('GameplayService: Tous les lieux ont déjà été découverts. Réinitialisation pour permettre de rejouer.');

            // On relance la sélection sans le filtre whereNotIn
            return $this->selectionnerLieuxSansFiltre($environnementId, $nbEnigmes, $difficulte);
        }

        return $lieux;
    }

    /**
     * Sélection classique (fallback si tout est déjà découvert)
     */
    private function selectionnerLieuxSansFiltre(int $environnementId, int $nbEnigmes, int $difficulte)
    {
        $typeEnigme = match ($difficulte) {
            1 => 'force1',
            2 => 'force2',
            3 => 'force3',
            default => 'force2',
        };

        $lieux = Lieu::where('environnement_id', $environnementId)
            ->where('actif', true)
            ->whereHas('enigmes', function ($query) use ($typeEnigme) {
                $query->where('type', $typeEnigme)->where('actif', true);
            })
            ->orderBy('ordre', 'asc')
            ->limit($nbEnigmes)
            ->get();

        if ($lieux->count() < $nbEnigmes) {
            $idsExistants = $lieux->pluck('id')->toArray();
            $lieuxComplementaires = Lieu::where('environnement_id', $environnementId)
                ->where('actif', true)
                ->whereNotIn('id', $idsExistants)
                ->whereHas('enigmes', function ($query) {
                    $query->where('actif', true);
                })
                ->orderBy('ordre', 'asc')
                ->limit($nbEnigmes - $lieux->count())
                ->get();
            $lieux = $lieux->concat($lieuxComplementaires)->sortBy('ordre');
        }

        return $lieux;
    }

    /**
     * Calcule le nombre d'énigmes selon durée et locomotion
     */
    private function calculerNombreEnigmes(int $dureeMinutes, string $locomotion): int
    {
        // Temps moyen par énigme selon le mode de transport (en minutes)
        $tempsParEnigme = match ($locomotion) {
            'velo' => 7,
            'voiture' => 5,
            'moto' => 5,
            default => 10, // 'pied' et fallback
        };

        // Calcul de base
        $nbEnigmes = (int) floor($dureeMinutes / $tempsParEnigme);

        // Limites de sécurité
        $minEnigmes = 3;   // Minimum pour une partie intéressante
        $maxEnigmes = match ($locomotion) {
            'voiture', 'moto' => 15,
            default => 10,
        };

        return max($minEnigmes, min($nbEnigmes, $maxEnigmes));
    }

    /**
     * Action : valider la position GPS du joueur
     *
     * @param Partie $partie La partie en cours
     * @param float $lat Latitude du joueur
     * @param float $lng Longitude du joueur
     * @param float|null $precision Précision GPS
     * @return array Résultat de la validation
     */
    public function validerGps(Partie $partie, float $lat, float $lng, ?float $precision): array
    {
        $progression = $partie->progression;

        // Vérifie que la partie est active
        if ($progression->estTerminee()) {
            return ['succes' => false, 'message' => 'La partie est terminée.'];
        }

        // --- ANTI-TRICHE ---
        if ($progression->last_lat && $progression->last_lng && $progression->last_validated_at) {
            $distanceKm = $this->gpsService->calculerDistance($progression->last_lat, $progression->last_lng, $lat, $lng) / 1000;
            $tempsHeures = now()->diffInSeconds($progression->last_validated_at) / 3600;
            
            if ($tempsHeures > 0) {
                $vitesse = $distanceKm / $tempsHeures;
                if ($vitesse > 120) {
                    \Log::warning('ANTI-TRICHE: Vitesse suspecte détectée', [
                        'user_id' => auth()->id(),
                        'partie_id' => $partie->id,
                        'vitesse' => $vitesse,
                        'distance_km' => $distanceKm,
                        'temps_heures' => $tempsHeures
                    ]);
                    return ['succes' => false, 'message' => 'Vitesse de déplacement incohérente détectée. Validation bloquée par sécurité.'];
                }
            }
        }

        // Récupère le lieu de l'énigme courante
        $enigme = $progression->enigmeCourante;
        if (!$enigme) {
            return ['succes' => false, 'message' => 'Aucune énigme en cours.'];
        }

        $lieu = $enigme->lieu;
        if (!$lieu) {
            return ['succes' => false, 'message' => 'Lieu de l\'énigme introuvable.'];
        }

        $lieuId = $lieu->id;

        // Validation GPS via le service dédié (coordonnées + rayon_metres du lieu en BDD)
        $resultat = $this->gpsService->validerPosition($lat, $lng, $precision, $lieu);

        // Si succès, marque l'énigme comme résolue et crédite les points
        if ($resultat['succes']) {
            $pointsGagnes = $progression->resoudreEnigmeCourante();
            
            // Mise à jour des infos anti-triche
            $progression->update([
                'last_lat' => $lat,
                'last_lng' => $lng,
                'last_validated_at' => now(),
            ]);

            $progression->refresh();

            $aSuivante = $progression->passerEnigmeSuivante();

            $resultat['enigme_resolue'] = true;
            $resultat['partie_terminee'] = !$aSuivante;
            $resultat['lieu_id'] = $lieuId;
            $resultat['points_gagnes'] = $pointsGagnes;
            $resultat['score_total'] = $progression->score;
            $resultat['lieu_nom'] = $lieu->nom;
        }

        return $resultat;
    }

    /**
     * Action : passer l'énigme courante (sans la résoudre)
     *
     * @param Partie $partie La partie en cours
     * @return array Résultat de l'action
     */
    public function passerEnigme(Partie $partie): array
    {
        $progression = $partie->progression;

        if ($progression->estTerminee()) {
            return ['succes' => false, 'message' => 'La partie est terminée.'];
        }

        $aSuivante = $progression->passerEnigmeSuivante();

        return [
            'succes' => true,
            'message' => $aSuivante
                ? 'Énigme passée. Voici la suivante.'
                : 'Plus d\'énigmes. Partie terminée.',
            'partie_terminee' => !$aSuivante,
        ];
    }

    /**
     * Action : demander un indice pour l'énigme courante
     *
     * @param Partie $partie La partie en cours
     * @return array Indice et pénalité éventuelle
     */
    public function demanderIndice(Partie $partie): array
    {
        $progression = $partie->progression;
        $enigme = $progression->enigmeCourante;

        if (!$enigme) {
            return ['succes' => false, 'message' => 'Aucune énigme en cours.'];
        }

        $indice = trim((string) ($enigme->indice ?? ''));
        if ($indice === '') {
            return [
                'succes' => false,
                'message' => 'Aucun indice n\'est disponible pour cette énigme.',
            ];
        }

        return [
            'succes' => true,
            'indice' => $indice,
            'message' => 'Voici un indice pour vous aider...',
        ];
    }

    /**
     * Retourne la solution de l'énigme courante sans avancer la progression.
     */
    public function solutionEnigmeCourante(Partie $partie): array
    {
        $progression = $partie->progression;

        if ($progression->estTerminee()) {
            return ['succes' => false, 'message' => 'La partie est terminée.'];
        }

        $enigme = $progression->enigmeCourante;
        if (!$enigme) {
            return ['succes' => false, 'message' => 'Aucune énigme en cours.'];
        }

        $solution = $this->texteSolutionEnigme($enigme);
        if ($solution === '') {
            return ['succes' => false, 'message' => 'Aucune solution n\'est disponible pour cette énigme.'];
        }

        return [
            'succes' => true,
            'solution' => $solution,
        ];
    }

    /**
     * Action : révéler la solution de l'énigme courante
     * Marque l'énigme comme non résolue et passe à la suivante
     *
     * @param Partie $partie La partie en cours
     * @return array Solution et statut
     */
    public function revelerSolution(Partie $partie): array
    {
        $resultat = $this->solutionEnigmeCourante($partie);
        if (!$resultat['succes']) {
            return $resultat;
        }

        $progression = $partie->progression;
        $aSuivante = $progression->passerEnigmeSuivante();

        return [
            'succes' => true,
            'solution' => $resultat['solution'],
            'message' => 'Solution révélée. L\'énigme est marquée comme non résolue.',
            'partie_terminee' => !$aSuivante,
        ];
    }

    private function texteSolutionEnigme(Enigme $enigme): string
    {
        $solution = trim((string) ($enigme->solution ?? ''));
        if ($solution !== '') {
            return $solution;
        }

        $reponse = trim((string) ($enigme->reponse ?? ''));
        if ($reponse !== '') {
            return $reponse;
        }

        // Fallback ultime : si c'est un lieu, on donne le nom du lieu
        if ($enigme->lieu) {
            return "La solution se trouve à : " . $enigme->lieu->nom;
        }

        return '';
    }

    /**
     * Met la partie en pause
     */
    public function mettreEnPause(Partie $partie): void
    {
        $partie->progression->mettreEnPause();
    }

    /**
     * Reprend une partie en pause
     */
    public function reprendre(Partie $partie): void
    {
        $partie->progression->reprendre();
    }

    /**
     * Termine la partie (abandon ou fin normale)
     */
    public function terminerPartie(Partie $partie): void
    {
        $partie->progression->terminer();
        $partie->statut = 'terminee';
        $partie->ended_at = now();
        $partie->save();
    }
}
