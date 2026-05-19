<?php

namespace Database\Seeders;

use App\Models\Environnement;
use App\Models\Partie;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Joueur Test',
            'email' => 'test@cityplay.fr',
            'password' => Hash::make('password'),
            'pseudo' => 'TestPlayer',
            'consent_cgu' => true,
            'consent_donnees' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Utilisateur test créé : test@cityplay.fr / password');

        $environnement = Environnement::where('nom', 'Bordeaux Patrimoine')->first();

        $partie = Partie::create([
            'createur_id' => $user->id,
            'environnement_id' => $environnement->id,
            'mode' => 'solo',
            'parametres' => [
                'duree' => 60,
                'locomotion' => 'pied',
                'difficulte' => 2,
                'nb_joueurs' => 1,
            ],
            'statut' => 'en_attente',
            'code_liaison' => null,
        ]);

        $this->command->info('Partie démo créée (ID: ' . $partie->id . ')');
        $this->command->info('');
        $this->command->info('🎮 PARCOURS DÉMO PRÊT !');
        $this->command->info('1. POST /api/parties/' . $partie->id . '/demarrer');
        $this->command->info('2. POST /api/parties/' . $partie->id . '/valider-gps (lat:48.8584, lng:2.2945)');
        $this->command->info('3. POST /api/parties/' . $partie->id . '/passer-enigme');
        $this->command->info('4. Répéter pour les 5 lieux...');
    }
}
