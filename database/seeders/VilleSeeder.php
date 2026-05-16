<?php

namespace Database\Seeders;

use App\Models\Ville;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VilleSeeder extends Seeder
{
    public function run(): void
    {
        Ville::create([
            'nom' => 'Paris',
            'slug' => Str::slug('Paris'),
        ]);

        $this->command->info('Ville "Paris" créée.');
    }
}
