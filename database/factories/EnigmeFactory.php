<?php

namespace Database\Factories;

use App\Models\Enigme;
use App\Models\Lieu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enigme>
 */
class EnigmeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lieu_id' => Lieu::factory(),
            'type' => 'force1',
            'titre' => 'Enigme Default',
            'texte' => 'Trouvez la réponse à cette énigme.',
            'reponse' => 'reponse',
            'points' => 10,
            'actif' => true,
        ];
    }
}
