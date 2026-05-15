<?php

namespace Database\Seeders;

use App\Models\Enigme;
use App\Models\Environnement;
use App\Models\Lieu;
use Illuminate\Database\Seeder;

class LieuSeeder extends Seeder
{
    public function run(): void
    {
        $environnement = Environnement::first();

        $lieux = [
            [
                'nom'          => 'Place de la Bourse',
                'ordre'        => 1,
                'latitude'     => 44.84139,
                'longitude'    => -0.56978,
                'rayon_metres' => 50,
                'description'  => 'La Place de la Bourse est l\'un des plus beaux exemples d\'architecture classique de Bordeaux, construite au XVIIIe siècle. Face à la Garonne, elle abrite le célèbre miroir d\'eau.',
            ],
            [
                'nom'          => 'Cathédrale Saint-André',
                'ordre'        => 2,
                'latitude'     => 44.83761,
                'longitude'    => -0.57889,
                'rayon_metres' => 60,
                'description'  => 'Cathédrale gothique classée au patrimoine mondial de l\'UNESCO. Sa construction s\'étend du XIe au XVIe siècle. La tour Pey-Berland, qui lui fait face, offre un panorama exceptionnel.',
            ],
            [
                'nom'          => 'Grosse Cloche',
                'ordre'        => 3,
                'latitude'     => 44.83541,
                'longitude'    => -0.57369,
                'rayon_metres' => 30,
                'description'  => 'La Grosse Cloche est l\'une des plus anciennes cloches de France (1775). Vestige des anciennes portes médiévales de la ville, elle servait autrefois à annoncer le début des vendanges.',
            ],
            [
                'nom'          => 'Grand Théâtre',
                'ordre'        => 4,
                'latitude'     => 44.84278,
                'longitude'    => -0.57428,
                'rayon_metres' => 40,
                'description'  => 'Chef-d\'œuvre du néoclassicisme construit en 1780 par l\'architecte Victor Louis. Ses 12 colonnes corinthiennes et son grand escalier ont inspiré Charles Garnier pour l\'Opéra de Paris.',
            ],
            [
                'nom'          => 'Porte Cailhau',
                'ordre'        => 5,
                'latitude'     => 44.84042,
                'longitude'    => -0.56972,
                'rayon_metres' => 30,
                'description'  => 'Porte fortifiée du XVe siècle, érigée en l\'honneur de la victoire de Charles VIII à Fornoue (1495). Accès au sommet pour une vue imprenable sur la Garonne et les quais.',
            ],
        ];

        foreach ($lieux as $lieuData) {
            $lieu = Lieu::create(array_merge($lieuData, [
                'environnement_id' => $environnement->id,
            ]));
            $this->seedEnigmes($lieu);
        }
    }
    private function seedEnigmes(Lieu $lieu): void
    {
        $templates = [
            'force3' => [
                'texte' => "Je suis gardien d'un fleuve et miroir de pierre,\n"
                    . "Mon reflet danse sur l'eau quand le vent fait sa prière.\n"
                    . "Construite par un roi pour honorer le commerce et les arts,\n"
                    . "Ma façade symétrique regarde vers {$lieu->nom} et ses remparts.\n"
                    . "Quel est ce lieu historique ?",
            ],
            'force2' => [
                'texte' => "Ce monument de {$lieu->nom} fut construit au XVIIIe ou XVe siècle "
                    . "et est aujourd'hui classé monument historique. "
                    . "Il se trouve à moins de 5 minutes à pied du centre-ville. "
                    . "Rendez-vous au point indiqué sur votre carte pour valider votre présence.",
            ],
            'force1' => [
                'texte' => "Vous cherchez : {$lieu->nom}.\n"
                    . "C'est un monument emblématique de Bordeaux.\n"
                    . "Suivez votre GPS jusqu'aux coordonnées indiquées. "
                    . "Activez le bouton « Valider sur place » quand vous y êtes !",
            ],
            'enfant' => [
                'texte' => "🗺️ Mission explorateur !\n\n"
                    . "Tu cherches un endroit très vieux et très beau qui s'appelle « {$lieu->nom} ».\n"
                    . "Demande à un adulte de t'aider à suivre la flèche sur l'écran.\n"
                    . "Quand tu arrives devant, appuie sur le gros bouton vert ! 🎉",
            ],
        ];

        foreach ($templates as $type => $fields) {
            Enigme::create([
                'lieu_id'    => $lieu->id,
                'type'       => $type,
                'texte'      => $fields['texte'],
                'image_url' => null, // Placeholder : images à ajouter via l'admin
            ]);
        }
    }
}
