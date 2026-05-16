<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder
 * 
 * Lance tous les seeders dans l'ordre de dépendance :
 * 1. Ville
 * 2. Environnement (dépend de Ville)
 * 3. Lieu (dépend de Environnement)
 * 4. Énigme (dépend de Lieu)
 * 5. Demo (dépend de tout le reste)
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            VilleSeeder::class,
            EnvironnementSeeder::class,
            LieuSeeder::class,
            EnigmeSeeder::class,
            DemoSeeder::class,        // ← AJOUTÉ
        ]);

        $this->command->info('');
        $this->command->info('✅ Seed complet terminé !');
        $this->command->info('Données démo : 1 ville, 1 environnement, 5 lieux, 20 énigmes, 1 utilisateur, 1 partie.');
    }
}
