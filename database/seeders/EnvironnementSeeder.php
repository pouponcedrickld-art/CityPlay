<?php

namespace Database\Seeders;

use App\Models\Environnement;
use App\Models\Ville;
use Illuminate\Database\Seeder;

class EnvironnementSeeder extends Seeder
{
    public function run(): void
    {
        $ville = Ville::where('nom', 'Paris')->first();

        Environnement::create([
            'ville_id' => $ville->id,
            'nom' => 'CityPlay Paris Centre',
            'retention_profils_jours' => 90,
            'duree_vie_lien_heures' => 24,
            'messages' => json_encode([
                'bonne_reponse' => 'Bravo ! Découvrez ce lieu magnifique.',
                'mauvaise_reponse' => 'Ce n\'est pas tout à fait ça. Essayez encore !',
                'fin' => 'Félicitations, vous avez terminé le parcours !',
            ]),
            'regles' => json_encode([
                'mode' => 'standard',
                'aide_active' => true,
            ]),
            'actif' => true,
        ]);

        $this->command->info('Environnement "CityPlay Paris Centre" créé.');
    }
}
