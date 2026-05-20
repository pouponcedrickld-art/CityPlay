<?php

namespace Database\Factories;

use App\Models\Lieu;
use App\Models\Environnement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lieu>
 */
class LieuFactory extends Factory
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
            'ordre' => 1,
            'actif' => true,
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'rayon_metres' => 50,
            'nom' => 'Lieu Default',
            'description' => 'Description du lieu default',
        ];
    }
}
