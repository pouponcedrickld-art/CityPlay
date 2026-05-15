<?php

namespace Database\Seeders;

use App\Models\Environnement;
use App\Models\Lieu;
use Illuminate\Database\Seeder;

class LieuSeeder extends Seeder
{
    public function run(): void
    {
        $environnement = Environnement::first();

        $lieux = [
            ['nom' => 'Place de l\'Étoile Rouge', 'ordre' => 1, 'latitude' => 6.3650, 'longitude' => 2.4180, 'rayon_metres' => 50, 'description' => 'Cœur historique de Cotonou.'],
            ['nom' => 'Port de Cotonou', 'ordre' => 2, 'latitude' => 6.3500, 'longitude' => 2.4250, 'rayon_metres' => 80, 'description' => 'Le plus grand port du Bénin.'],
            ['nom' => 'Marché Dantokpa', 'ordre' => 3, 'latitude' => 6.3700, 'longitude' => 2.4300, 'rayon_metres' => 60, 'description' => 'Le plus grand marché d\'Afrique de l\'Ouest.'],
            ['nom' => 'Plage de Fidjrossè', 'ordre' => 4, 'latitude' => 6.3400, 'longitude' => 2.3900, 'rayon_metres' => 100, 'description' => 'Magnifique plage de Cotonou.'],
        ];

        foreach ($lieux as $lieu) {
            Lieu::create(array_merge($lieu, [
                'environnement_id' => $environnement->id,
                'actif' => true,
            ]));
        }
    }
}