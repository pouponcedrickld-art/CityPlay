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
            'ville_id'                 => $ville->id,
            'nom'                      => 'Bordeaux Patrimoine',
            'retention_profils_jours'  => 90,
            'duree_vie_lien_heures'    => 24,
            'regles'                   => 'Respectez les lieux et les autres visiteurs. Bonne chance !',
            'message_bonne_reponse'    => 'Bravo ! Vous avez trouvé ce lieu remarquable de Bordeaux.',
            'message_mauvaise_reponse' => 'Pas encore ! Essayez un indice ou passez au lieu suivant.',
            'message_fin'              => 'Partie terminée ! Profitez d\'un verre de vin local pour célébrer. 🍷',
            'message_pause'            => 'Partie en pause. Vos progrès sont sauvegardés.',
        ]);
    }
}
