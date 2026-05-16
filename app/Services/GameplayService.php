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
     * @return ProgressionPartie La progression créée
     */
    public function initialiserPartie(Partie $partie): ProgressionPartie
    {
        // Récupère les paramètres de la partie
        $parametres = $partie->parametres;
        $dureeMinutes = $parametres['duree'] ?? 60;
        $locomotion = $parametres['locomotion'] ?? 'pied';
        $difficulte = $parametres['difficulte'] ?? 2;

        // === ÉTAPE 1 : Calcul du nombre d'énigmes ===
        $nbEnigmes = $this->calculerNombreEnigmes($dureeMinutes, $locomotion);

        // === ÉTAPE 2 : Sélection des lieux ===
        $lieux = $this->selectionnerLieux($partie->environnement_id, $nbEnigmes, $difficulte);

        // === ÉTAPE 3 : Création de la progression ===
        $progression = ProgressionPartie::create([
            'partie_id' => $partie->id,
            'lieux_a_visiter' => $lieux->pluck('id')->toArray(),
            'lieux_decouverts' => [],
            'lieux_restants' => $lieux->pluck('id')->toArray(),
            'enigme_courante_id' => $lieux->first()?->enigmes->first()?->id,
            'temps_restant' => $dureeMinutes * 60, // Conversion en secondes
            'score' => 0,
            'statut' => 'en_cours',
        ]);

        // Démarre la partie
        $partie->statut = 'en_cours';
        $partie->started_at = now();
        $partie->save();

        return $progression;
    }

    /**
     * Calcule le nombre d'énigmes selon durée et locomotion
     * 
     * Logique : 
     * - À pied : ~10 min par énigme (déplacement + résolution)
     * - Vélo : ~7 min par énigme
     * - Voiture : ~5 min par énigme (mais limité à 15 max)
     * 
     * @param int $dureeMinutes Durée totale souhaitée
     * @param string $locomotion 'pied', 'velo', 'voiture'
     * @return int Nombre d'énigmes à proposer
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
            'voiture', 'moto' => 15, // Trop de lieux en voiture = galère
            default => 10,
        };

        return max($minEnigmes, min($nbEnigmes, $maxEnigmes));
    }

    /**
     * Sélectionne les lieux pour une partie
     * 
     * Critères :
     * 1. Ordre défini par la mairie (champ 'ordre')
     * 2. Difficulté demandée (énigmes disponibles)
     * 3. Pas de doublon
     * 
     * @param int $environnementId ID de l'environnement
     * @param int $nbEnigmes Nombre de lieux à sélectionner
     * @param int $difficulte Difficulté demandée (1, 2, 3)
     * @return \Illuminate\Database\Eloquent\Collection Lieux sélectionnés
     */
    private function selectionnerLieux(int $environnementId, int $nbEnigmes, int $difficulte)
    {
        // Récupère les lieux de l'environnement, ordonnés par la mairie
        // Vérifie qu'ils ont au moins une énigme de la difficulté demandée
        return Lieu::where('environnement_id', $environnementId)
            ->whereHas('enigmes', function ($query) use ($difficulte) {
                // Sélectionne selon la convention : 3 = dur, 1 = facile
                $typeEnigme = match ($difficulte) {
                    1 => 'force1',
                    2 => 'force2',
                    3 => 'force3',
                    default => 'force2',
                };
                $query->where('type', $typeEnigme);
            })
            ->orderBy('ordre', 'asc') // Ordre défini par la mairie
            ->limit($nbEnigmes)
            ->get();
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

        // Récupère le lieu de l'énigme courante
        $enigme = $progression->enigmeCourante;
        if (!$enigme) {
            return ['succes' => false, 'message' => 'Aucune énigme en cours.'];
        }

        $lieu = $enigme->lieu;

        // Validation GPS via le service dédié
        $resultat = $this->gpsService->validerPosition($lat, $lng, $precision, $lieu);

        // Si succès, marque l'énigme comme résolue
        if ($resultat['succes']) {
            $progression->resoudreEnigmeCourante();

            // Passe à l'énigme suivante
            $aSuivante = $progression->passerEnigmeSuivante();

            $resultat['enigme_resolue'] = true;
            $resultat['partie_terminee'] = !$aSuivante;
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

        // Pour l'instant, retourne le texte de l'énigme comme "indice"
        // TODO : ajouter un champ 'indice' séparé dans la table enigmes
        return [
            'succes' => true,
            'indice' => $enigme->texte, // Texte de l'énigme comme indice
            'message' => 'Voici un indice pour vous aider...',
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
        $progression = $partie->progression;
        $enigme = $progression->enigmeCourante;

        if (!$enigme) {
            return ['succes' => false, 'message' => 'Aucune énigme en cours.'];
        }

        // Récupère la solution (pour l'instant, le texte de l'énigme)
        // TODO : ajouter un champ 'solution' dans la table enigmes
        $solution = $enigme->texte;

        // Passe à l'énigme suivante sans incrémenter le score
        $aSuivante = $progression->passerEnigmeSuivante();

        return [
            'succes' => true,
            'solution' => $solution,
            'message' => 'Solution révélée. L\'énigme est marquée comme non résolue.',
            'partie_terminee' => !$aSuivante,
        ];
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