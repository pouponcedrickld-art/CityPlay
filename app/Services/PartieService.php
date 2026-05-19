<?php

namespace App\Services;

use App\Models\Environnement;
use App\Models\Lieu;
use App\Models\Partie;
use App\Models\Progression;
use App\Models\ProgressionPartie;
use App\Models\Team;
use Illuminate\Support\Str;

class PartieService
{
    /**
     * Créer une nouvelle partie complète
     */
    public function creerPartie(array $data, int $createurId)
    {
        \Log::info('PartieService: Début création partie', ['data' => $data, 'createurId' => $createurId]);
        
        $environnement = Environnement::findOrFail($data['environnement_id']);
        \Log::info('PartieService: Environnement trouvé', ['nom' => $environnement->nom]);

        // Génération du code de liaison
        $codeLiaison = Str::upper(Str::random(6));

        // Création de l'équipe si mode team
        $teamId = null;
        if ($data['mode'] === 'team') {
            \Log::info('PartieService: Création équipe');
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

        $partie = Partie::create([
            'environnement_id' => $environnement->id,
            'createur_id' => $createurId,
            'team_id' => $teamId,
            'mode' => $data['mode'],
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

        \Log::info('PartieService: Fin création partie', ['id' => $partie->id]);
        return $partie->load('environnement');
    }
}


