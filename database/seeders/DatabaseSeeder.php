<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            VilleSeeder::class,
            EnvironnementSeeder::class,
            LieuSeeder::class,
            DemoSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Seed complet terminé !');
    }
}
