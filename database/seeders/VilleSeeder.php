<?php

namespace Database\Seeders;

use App\Models\Ville;
use Illuminate\Database\Seeder;

class VilleSeeder extends Seeder
{
    public function run(): void
    {
        Ville::create([
            'nom' => 'Bordeaux',
            'slug' => 'bordeaux',
            'description' => 'Capitale de la Gironde, ville classée au patrimoine mondial de l\'UNESCO, célèbre pour son architecture du XVIIIe siècle et ses vignobles.',
        ]);

        $this->command->info('Ville "Bordeaux" créée.');
    }
}
