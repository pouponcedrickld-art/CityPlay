<?php

namespace Database\Factories;

use App\Models\Partie;
use App\Models\Environnement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Partie>
 */
class PartieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'environnement_id' => Environnement::factory(),
            'createur_id' => User::factory(),
            'mode' => 'solo',
            'parametres' => [
                'duree' => 60,
                'locomotion' => 'pied',
                'difficulte' => 2,
            ],
            'statut' => 'en_attente',
        ];
    }
}
