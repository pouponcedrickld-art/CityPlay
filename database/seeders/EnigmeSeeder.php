<?php

namespace Database\Seeders;

use App\Models\Enigme;
use App\Models\Lieu;
use Illuminate\Database\Seeder;

class EnigmeSeeder extends Seeder
{
    public function run(): void
    {
        $lieux = Lieu::all();

        foreach ($lieux as $lieu) {
            // Force 3 (Difficile)
            Enigme::firstOrCreate([
                'lieu_id' => $lieu->id,
                'type' => 'force3',
            ], [
                'texte' => 'Je suis un lieu très fréquenté où les couleurs et les odeurs envahissent tes sens. Où suis-je ?',
                'image_url' => null,
            ]);

            // Force 2 (Moyen)
            Enigme::firstOrCreate([
                'lieu_id' => $lieu->id,
                'type' => 'force2',
            ], [
                'texte' => 'Ici, on trouve de tout : tissus, épices et objets artisanaux.',
                'image_url' => null,
            ]);

            // Force 1 (Facile)
            Enigme::firstOrCreate([
                'lieu_id' => $lieu->id,
                'type' => 'force1',
            ], [
                'texte' => 'Un endroit parfait pour découvrir l\'histoire locale en s\'amusant.',
                'image_url' => null,
            ]);

            // Enfant
            Enigme::firstOrCreate([
                'lieu_id' => $lieu->id,
                'type' => 'enfant',
            ], [
                'texte' => 'Je suis un grand marché où tu peux trouver des jouets et des bonbons !',
                'image_url' => null,
            ]);
        }
    }
}
