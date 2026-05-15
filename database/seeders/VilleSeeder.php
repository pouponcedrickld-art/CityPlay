<?php

namespace Database\Seeders;

use App\Models\Ville;
use Illuminate\Database\Seeder;

class VilleSeeder extends Seeder
{
    public function run(): void
    {
        Ville::create([
            'nom' => 'Cotonou',
            'slug' => 'cotonou',
            'description' => 'La capitale économique du Bénin'
        ]);
    }
}