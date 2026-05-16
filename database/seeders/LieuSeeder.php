<?php

namespace Database\Seeders;

use App\Models\Environnement;
use App\Models\Lieu;
use Illuminate\Database\Seeder;

/**
 * LieuSeeder
 * 
 * Crée 5 lieux de démo à Paris avec coordonnées GPS réelles.
 */
class LieuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $environnement = Environnement::where('nom', 'CityPlay Paris Centre')->first();

        $lieux = [
            [
                'ordre' => 1,
                'nom' => 'Tour Eiffel',
                'latitude' => 48.8584,
                'longitude' => 2.2945,
                'rayon_metres' => 50,
                'description' => 'La Dame de Fer, symbole de Paris. Construite par Gustave Eiffel pour l\'Exposition universelle de 1889.',
            ],
            [
                'ordre' => 2,
                'nom' => 'Louvre',
                'latitude' => 48.8606,
                'longitude' => 2.3376,
                'rayon_metres' => 40,
                'description' => 'Le plus grand musée d\'art du monde. Ancien palais royal, abrite la Joconde.',
            ],
            [
                'ordre' => 3,
                'nom' => 'Notre-Dame',
                'latitude' => 48.8529,
                'longitude' => 2.3499,
                'rayon_metres' => 45,
                'description' => 'Cathédrale gothique du XIIe siècle. Cœur historique de Paris sur l\'Île de la Cité.',
            ],
            [
                'ordre' => 4,
                'nom' => 'Sacré-Cœur',
                'latitude' => 48.8867,
                'longitude' => 2.3431,
                'rayon_metres' => 55,
                'description' => 'Basilique blanche au sommet de Montmartre. Vue panoramique sur tout Paris.',
            ],
            [
                'ordre' => 5,
                'nom' => 'Panthéon',
                'latitude' => 48.8462,
                'longitude' => 2.3458,
                'rayon_metres' => 40,
                'description' => 'Mausolée des grandes figures de France. Ancienne église dédiée à Sainte-Geneviève.',
            ],
        ];

        foreach ($lieux as $lieu) {
            Lieu::create(array_merge($lieu, [
                'environnement_id' => $environnement->id,
            ]));
        }

        $this->command->info('5 lieux créés pour Paris Centre.');
    }
}
