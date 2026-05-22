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
        $environnement = Environnement::where('nom', 'Bordeaux Patrimoine')->first();

        if (!$environnement) {
            return;
        }

        $lieux = [
            [
                'nom' => 'Place de la Bourse',
                'ordre' => 1,
                'latitude' => 44.84139,
                'longitude' => -0.56978,
                'rayon_metres' => 50,
                'description' => 'Architecture classique du XVIIIe siècle face à la Garonne, célèbre pour son miroir d\'eau.',
            ],
            [
                'nom' => 'Cathédrale Saint-André',
                'ordre' => 2,
                'latitude' => 44.83761,
                'longitude' => -0.57889,
                'rayon_metres' => 60,
                'description' => 'Cathédrale gothique classée UNESCO, avec sa tour Pey-Berland indépendante.',
            ],
            [
                'nom' => 'Grosse Cloche',
                'ordre' => 3,
                'latitude' => 44.83541,
                'longitude' => -0.57369,
                'rayon_metres' => 30,
                'description' => 'Ancienne porte de ville du XVe siècle et l\'un des rares vestiges médiévaux.',
            ],
            [
                'nom' => 'Grand Théâtre',
                'ordre' => 4,
                'latitude' => 44.84278,
                'longitude' => -0.57428,
                'rayon_metres' => 40,
                'description' => 'Chef-d\'œuvre néoclassique de Victor Louis, célèbre pour ses colonnes corinthiennes.',
            ],
            [
                'nom' => 'Porte Cailhau',
                'ordre' => 5,
                'latitude' => 44.84042,
                'longitude' => -0.56972,
                'rayon_metres' => 30,
                'description' => 'Porte médiévale érigée en l\'honneur de Charles VIII au XVe siècle.',
            ],
            [
                'nom' => 'Monument aux Girondins',
                'ordre' => 6,
                'latitude' => 44.84556,
                'longitude' => -0.57500,
                'rayon_metres' => 50,
                'description' => 'Fontaine spectaculaire sur la place des Quinconces honorant les députés Girondins.',
            ],
            [
                'nom' => 'Pont de Pierre',
                'ordre' => 7,
                'latitude' => 44.83861,
                'longitude' => -0.56361,
                'rayon_metres' => 50,
                'description' => 'Le premier pont sur la Garonne, construit sur ordre de Napoléon Ier.',
            ],
            [
                'nom' => 'Basilique Saint-Michel',
                'ordre' => 8,
                'latitude' => 44.83444,
                'longitude' => -0.56528,
                'rayon_metres' => 60,
                'description' => 'Église gothique flamboyante connue pour sa flèche isolée, la deuxième plus haute de France.',
            ],
            [
                'nom' => 'Palais Gallien',
                'ordre' => 9,
                'latitude' => 44.84806,
                'longitude' => -0.58139,
                'rayon_metres' => 40,
                'description' => 'Vestiges d\'un amphithéâtre romain du IIe siècle, témoin de l\'époque Burdigala.',
            ],
            [
                'nom' => 'Place de la Victoire',
                'ordre' => 10,
                'latitude' => 44.83056,
                'longitude' => -0.57333,
                'rayon_metres' => 50,
                'description' => 'Place centrale ornée d\'une porte monumentale et de sculptures de tortues géantes.',
            ],
            [
                'nom' => 'Cité du Vin',
                'ordre' => 11,
                'latitude' => 44.86222,
                'longitude' => -0.55028,
                'rayon_metres' => 70,
                'description' => 'Architecture futuriste dédiée à la culture et aux civilisations du vin.',
            ],
            [
                'nom' => 'Jardin Public',
                'ordre' => 12,
                'latitude' => 44.84778,
                'longitude' => -0.57778,
                'rayon_metres' => 100,
                'description' => 'Havre de paix classé "Jardin Remarquable", créé en 1746.',
            ],
            [
                'nom' => 'Marché des Capucins',
                'ordre' => 13,
                'latitude' => 44.83083,
                'longitude' => -0.56722,
                'rayon_metres' => 60,
                'description' => 'Le "ventre de Bordeaux", un marché couvert vibrant et authentique.',
            ],
            [
                'nom' => 'Base Sous-Marine',
                'ordre' => 14,
                'latitude' => 44.87028,
                'longitude' => -0.55833,
                'rayon_metres' => 100,
                'description' => 'Ancienne base navale de la 2nde Guerre Mondiale reconvertie en espace culturel.',
            ],
            [
                'nom' => 'Darwin Ecosystème',
                'ordre' => 15,
                'latitude' => 44.84944,
                'longitude' => -0.55944,
                'rayon_metres' => 80,
                'description' => 'Lieu alternatif et éco-responsable situé dans une ancienne caserne militaire.',
            ],
        ];

        foreach ($lieux as $lieuData) {
            $lieu = Lieu::updateOrCreate(
                ['nom' => $lieuData['nom'], 'environnement_id' => $environnement->id],
                $lieuData
            );
            $this->seedEnigmes($lieu);
        }
    }

    private function seedEnigmes(Lieu $lieu): void
    {
        $templates = [
            'force3' => [
                'titre' => 'Énigme de Maître',
                'texte' => "Je suis le gardien d'un secret bordelais,\n"
                         . "Mon architecture raconte des siècles de succès.\n"
                         . "Regardez autour de vous pour trouver le mot caché,\n"
                         . "Ma silhouette se dresse près de {$lieu->nom}, bien ancrée.\n"
                         . "Quel est ce monument ?",
                'reponse' => 'bordeaux',
                'solution' => "Félicitations ! Vous avez décrypté l'énigme complexe de {$lieu->nom}.",
                'points' => 100,
                'indice' => "Observez les détails architecturaux au sommet de l'édifice.",
            ],
            'force2' => [
                'titre' => 'Découverte Culturelle',
                'texte' => "Le lieu « {$lieu->nom} » est une étape majeure de ce parcours. "
                         . "Utilisez votre carte pour vous rapprocher du point exact. "
                         . "Une fois sur place, cherchez un détail qui vous confirmera que vous y êtes.",
                'reponse' => 'valider',
                'solution' => "Bien joué ! Vous avez localisé {$lieu->nom}.",
                'points' => 60,
                'indice' => "Le point sur la carte est précis, fiez-vous à lui.",
            ],
            'force1' => [
                'titre' => 'Mission Express',
                'texte' => "Destination : {$lieu->nom}.\n"
                         . "Rendez-vous rapidement sur place pour marquer des points.\n"
                         . "Appuyez sur le bouton de validation dès que le GPS confirme votre position.",
                'reponse' => 'oui',
                'solution' => "Validation réussie pour {$lieu->nom}.",
                'points' => 30,
                'indice' => "Suivez simplement la direction indiquée par le GPS.",
            ],
            'enfant' => [
                'titre' => 'Chasse au Trésor',
                'texte' => "🧒 Mission pour toi !\n\n"
                         . "Tu dois trouver l'endroit appelé « {$lieu->nom} ».\n"
                         . "C'est un endroit très spécial ! Dès que tu es devant, demande à l'adulte d'appuyer sur le bouton magique.",
                'reponse' => 'trouvé',
                'solution' => "Bravo petit explorateur ! Tu as trouvé {$lieu->nom} !",
                'points' => 120,
                'indice' => "C'est un grand monument, tu ne peux pas le rater !",
            ],
        ];

        foreach ($templates as $type => $fields) {
            Enigme::updateOrCreate(
                ['lieu_id' => $lieu->id, 'type' => $type],
                [
                    'titre' => $fields['titre'],
                    'texte' => $fields['texte'],
                    'reponse' => $fields['reponse'],
                    'solution' => $fields['solution'],
                    'points' => $fields['points'],
                    'indice' => $fields['indice'],
                    'actif' => true,
                ]
            );
        }
    }
}
