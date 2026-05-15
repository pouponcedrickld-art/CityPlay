<?php

namespace Database\Seeders;

use App\Models\Environnement;
use App\Models\Ville;
use Illuminate\Database\Seeder;

class EnvironnementSeeder extends Seeder
{
    public function run(): void
    {
        $ville = Ville::first();

        Environnement::create([
            'ville_id' => $ville->id,
            'nom' => 'Cotonou Découverte',
            'retention_profils_jours' => 90,
            'duree_vie_lien_heures' => 48,
            'messages' => [
                'bonne_reponse' => 'Bravo ! Vous avez découvert ce lieu magnifique.',
                'fin_partie' => 'Merci d\'avoir joué à CityPlay !'
            ],
            'actif' => true,
        ]);
    }
}