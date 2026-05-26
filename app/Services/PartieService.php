<?php

namespace App\Services;

use App\Models\Environnement;
use App\Models\Lieu;
use App\Models\Partie;
use App\Models\Progression;
use App\Models\ProgressionPartie;
use App\Models\Team;
use Illuminate\Support\Str;

/**
 * Service gérant la logique métier de création et configuration des parties.
 * 
 * Centralise la création simultanée d'une session de jeu (Partie)
 * et d'une équipe (Team) si nécessaire.
 */
class PartieService
{
    /**
     * Crée une nouvelle partie complète avec son équipe associée.
     * 
     * @param array $data Données du formulaire (mode, durée, locomotion, etc.)
     * @param int $createurId ID de l'utilisateur qui lance la partie
     * @return Partie La partie créée avec ses relations chargées
     */
    public function creerPartie(array $data, int $createurId)
    {
        \Log::info('PartieService: Début création partie', ['data' => $data, 'createurId' => $createurId]);
        
        // 1. Récupération de l'environnement choisi
        $environnement = Environnement::findOrFail($data['environnement_id']);
        \Log::info('PartieService: Environnement trouvé', ['nom' => $environnement->nom]);

        // 2. Génération du code de liaison unique (6 caractères alphanumériques)
        $codeLiaison = Str::upper(Str::random(6));

        // 3. Création de l'équipe si le mode de jeu est "équipe"
        $teamId = null;
        if ($data['mode'] === 'team') {
            \Log::info('PartieService: Création équipe');
            
            // Récupère le créateur pour générer un nom d'équipe par défaut
            $createur = \App\Models\User::find($createurId);
            $nomCreateur = $createur?->name ?? $createur?->pseudo ?? 'Joueur';
            
            // Génère un nom d'équipe unique
            $nomEquipe = 'Équipe ' . $nomCreateur;
            $compteur = 1;
            while (Team::where('nom', $nomEquipe)->exists()) {
                $nomEquipe = 'Équipe ' . $nomCreateur . ' (' . $compteur . ')';
                $compteur++;
            }
            
            $team = Team::create([
                'nom' => $nomEquipe,
                'description' => 'Équipe créée pour une partie en groupe',
                'createur_id' => $createurId,
                'max_joueurs' => $data['nb_joueurs'] ?? 10,
                'code_liaison' => $codeLiaison,
                'environnement_id' => $environnement->id,
            ]);
            $teamId = $team->id;

            // Le créateur est automatiquement ajouté avec le rôle 'challenger' (chef d'équipe)
            $team->users()->attach($createurId, ['role' => 'challenger']);
            
            \Log::info('PartieService: Équipe créée avec succès', [
                'team_id' => $teamId,
                'nom' => $nomEquipe,
            ]);
        }

        // 4. Création de la session de jeu principale
        $partie = Partie::create([
            'environnement_id' => $environnement->id,
            'createur_id' => $createurId,
            'team_id' => $teamId,
            'mode' => $data['mode'],
            'ordre_jeu' => $data['ordre_jeu'] ?? 'lineaire',
            'lat_depart' => $data['lat_depart'] ?? null,
            'lng_depart' => $data['lng_depart'] ?? null,
            'parametres' => [
                'duree' => $data['duree'],
                'locomotion' => $data['locomotion'],
                'difficulte' => $data['difficulte'] ?? 2,
                'nb_joueurs' => $data['nb_joueurs'] ?? 1,
            ],
            'statut' => 'en_attente',
            'code_liaison' => $codeLiaison,
            'expire_at' => now()->addHours($environnement->duree_vie_lien_heures ?? 24),
        ]);

        // 5. Mise à jour avec le lien de partage complet
        $partie->update([
            'lien_partage' => $partie->genererLienPartage(),
        ]);

        \Log::info('PartieService: Fin création partie', ['id' => $partie->id]);
        return $partie->load('environnement');
    }
}


