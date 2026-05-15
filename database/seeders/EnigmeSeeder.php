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
            Enigme::create([
                'lieu_id' => $lieu->id,
                'type' => 'force3',
                'titre' => 'Le Mystère du Marché',
                'texte' => 'Je suis un lieu très fréquenté où les couleurs et les odeurs envahissent tes sens. Où suis-je ?',
                'points' => 150,
                'image_url' => null,
            ]);

            Enigme::create([
                'lieu_id' => $lieu->id,
                'type' => 'force2',
                'titre' => 'L\'Écho des Tissus',
                'texte' => 'Ici, on trouve de tout : tissus, épices et objets artisanaux.',
                'points' => 100,
                'image_url' => null,
            ]);

            Enigme::create([
                'lieu_id' => $lieu->id,
                'type' => 'enfant',
                'titre' => 'Chasse aux Bonbons',
                'texte' => 'Je suis un grand marché où tu peux trouver des jouets et des bonbons !',
                'points' => 50,
                'image_url' => null,
            ]);
        }
    }
}