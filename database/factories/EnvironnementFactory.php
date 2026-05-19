<?php

namespace Database\Factories;

use App\Models\Environnement;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Environnement>
 */
class EnvironnementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ville_id' => function() {
                return Ville::firstOrCreate(
                    ['nom' => 'Default Ville'],
                    ['slug' => 'default-ville', 'description' => 'Description de la ville']
                )->id;
            },
            'nom' => 'Default Environnement',
            'retention_profils_jours' => 90,
            'duree_vie_lien_heures' => 24,
            'message_bonne_reponse' => 'Bravo ! Bonne réponse.',
            'message_mauvaise_reponse' => 'Mauvaise réponse. Réessayez.',
            'message_pause' => 'Partie en pause.',
            'message_fin' => 'Félicitations, partie terminée !',
        ];
    }
}
