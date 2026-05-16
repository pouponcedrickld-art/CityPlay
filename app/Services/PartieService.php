<?php

namespace App\Services;

use App\Models\Environnement;
use App\Models\Lieu;
use App\Models\Partie;
use App\Models\Progression;
use App\Models\Team;
use Illuminate\Support\Str;

class PartieService
{
    /**
     * Créer une nouvelle partie complète
     */
    public function creerPartie(array $data, int $createurId)
    {
        $environnement = Environnement::findOrFail($data['environnement_id']);

        // Génération du code de liaison
        $codeLiaison = Str::upper(Str::random(6));

        // Création de l'équipe si mode team
        $teamId = null;
        if ($data['mode'] === 'team') {
            $team = Team::create([
                'nom' => 'Groupe de ' . auth()->user()->name,
                'createur_id' => $createurId,
                'max_joueurs' => $data['nb_joueurs'] ?? 10,
                'code_liaison' => $codeLiaison,
            ]);
            $teamId = $team->id;

            // Ajout du créateur à l'équipe
            $team->users()->attach($createurId, ['role' => 'challenger']);
        }

        // Calcul du nombre d'énigmes
        $nbEnigmes = $this->calculerNombreEnigmes(
            $data['duree_prevue'],
            $data['locomotion']
        );

        // Sélection des lieux selon ordre + difficulté
        $lieuxSelectionnes = $this->selectionnerLieux(
            $environnement,
            $nbEnigmes,
            $data['difficulte'] ?? 2
        );

        $partie = Partie::create([
            'environnement_id' => $environnement->id,
            'createur_id' => $createurId,
            'team_id' => $teamId,
            'mode' => $data['mode'],
            'parametres' => [
                'duree_prevue' => $data['duree_prevue'],
                'locomotion' => $data['locomotion'],
                'difficulte' => $data['difficulte'] ?? $environnement->difficulte ?? 2,
                'nb_joueurs' => $data['nb_joueurs'] ?? 1,
                'nb_enigmes' => $nbEnigmes,
            ],
            'score_total' => 0,
            'code_liaison' => $codeLiaison,
            'statut' => 'en_attente',
            'expire_at' => now()->addHours($environnement->duree_vie_lien_heures),
        ]);

        // Création de la progression pour le créateur
        Progression::create([
            'partie_id' => $partie->id,
            'user_id' => $createurId,
            'lieux_a_visiter' => $lieuxSelectionnes->pluck('id')->toArray(),
            'lieux_decouverts' => [],
            'lieux_restants' => $lieuxSelectionnes->pluck('id')->toArray(),
            'enigme_courante_id' => null,
            'temps_restant_minutes' => $data['duree_prevue'],
            'nb_enigmes_resolues' => 0,
            'score' => 0,
        ]);

        return $partie->load('environnement');
    }

    /**
     * Calcul du nombre d'énigmes selon durée + locomotion
     */
    private function calculerNombreEnigmes(int $dureeMinutes, string $locomotion): int
    {
        $vitesseMoyenne = match ($locomotion) {
            'pied' => 4,   // km/h
            'velo' => 12,
            'voiture' => 25,
            default => 5,
        };

        // Estimation : 1 énigme ≈ 12 à 20 minutes (déplacement + résolution)
        $tempsParEnigme = match ($locomotion) {
            'pied' => 18,
            'velo' => 15,
            'voiture' => 12,
            default => 18,
        };

        $nb = (int) floor($dureeMinutes / $tempsParEnigme);

        return max(3, min(12, $nb)); // entre 3 et 12 énigmes
    }

    /**
     * Sélection des lieux selon ordre mairie + difficulté
     */
    private function selectionnerLieux(Environnement $environnement, int $nombre, int $difficulte)
    {
        return Lieu::where('environnement_id', $environnement->id)
            ->where('actif', true)
            ->orderBy('ordre')
            ->take($nombre)
            ->get();
    }

    /**
     * Passer à l'énigme suivante (à utiliser plus tard)
     */
    public function passerEnigmeSuivante(Progression $progression)
    {
        // Logique future
    }
}
