<?php

namespace Database\Seeders;

use App\Models\Ville;
use App\Models\Environnement;
use App\Models\Lieu;
use App\Models\Enigme;
use Illuminate\Database\Seeder;

class CotonouSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer la ville de Cotonou
        $ville = Ville::updateOrCreate(
            ['slug' => 'cotonou'],
            [
                'nom' => 'Cotonou',
                'description' => 'La capitale économique du Bénin, entre lagune et océan.'
            ]
        );

        // 2. Créer l'environnement (Parcours)
        $environnement = Environnement::updateOrCreate(
            ['nom' => 'Les Trésors de Cotonou', 'ville_id' => $ville->id],
            [
                'actif' => true,
                'retention_profils_jours' => 30,
                'duree_vie_lien_heures' => 24,
                'message_bonne_reponse' => 'Excellent ! Vous avez l\'œil d\'un véritable explorateur.',
                'message_mauvaise_reponse' => 'Ce n\'est pas tout à fait ça... Regardez mieux autour de vous.',
                'message_fin' => 'Félicitations ! Vous avez terminé le parcours complet de Cotonou.',
                'message_pause' => 'Partie en pause. Revenez vite pour la suite !',
            ]
        );

        // 3. Liste des Lieux de Cotonou
        $lieux = [
            [
                'nom' => 'Place de l\'Étoile Rouge',
                'ordre' => 1,
                'latitude' => 6.370500,
                'longitude' => 2.417200,
                'rayon_metres' => 50,
                'description' => 'Un monument emblématique en briques rouges et forme circulaire, symbolisant l\'histoire politique du Bénin.',
            ],
            [
                'nom' => 'Esplanade de l\'Amazone',
                'ordre' => 2,
                'latitude' => 6.348600,
                'longitude' => 2.428100,
                'rayon_metres' => 60,
                'description' => 'Une majestueuse esplanade accueillant la statue de l\'Amazone de 30 mètres de haut, symbole de la bravoure féminine.',
            ],
            [
                'nom' => 'Fondation Zinsou',
                'ordre' => 3,
                'latitude' => 6.353300,
                'longitude' => 2.428800,
                'rayon_metres' => 30,
                'description' => 'Un espace d\'art contemporain africain d\'accès gratuit, niché dans une superbe villa historique de style afro-brésilien.',
            ],
            [
                'nom' => 'Cathédrale Notre-Dame',
                'ordre' => 4,
                'latitude' => 6.353900,
                'longitude' => 2.433800,
                'rayon_metres' => 40,
                'description' => 'Un édifice religieux remarquable par son architecture rayée de rouge et de blanc unique, situé près de l\'Ancien Pont.',
            ],
            [
                'nom' => 'Marché Dantokpa',
                'ordre' => 5,
                'latitude' => 6.372500,
                'longitude' => 2.436100,
                'rayon_metres' => 100,
                'description' => "Le plus grand marché à ciel ouvert de l'Afrique de l'Ouest, bordant la lagune de Cotonou.",
            ],
            [
                'nom' => 'Embarcadère de la Lagune',
                'ordre' => 6,
                'latitude' => 6.446800,
                'longitude' => 2.424400,
                'rayon_metres' => 50,
                'description' => 'Le point de départ des pirogues traditionnelles naviguant vers la célèbre cité lacustre sur pilotis de Ganvié.',
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
        // En se basant sur le modèle de Bordeaux avec TES types en base de données
        $templates = [
            'force3' => [
                'titre' => 'Le Secret des Pierres',
                'texte' => "Je suis le gardien secret d'une histoire fascinante,\n"
                         . "Mon architecture et mes secrets attendent votre présence vigilante.\n"
                         . "Regardez autour de vous pour percer mes mystères les plus beaux,\n"
                         . "Ma silhouette se dresse fièrement pour guider le voyage de {$lieu->nom}.\n"
                         . "Quel est ce lieu historique ?",
                'reponse' => 'cotonou',
                'solution' => "Félicitations ! Vous avez décrypté l'histoire entourant {$lieu->nom}.",
                'points' => 70,
            ],
            'force2' => [
                'titre' => 'Repère Historique',
                'texte' => "Ce monument emblématique nommé « {$lieu->nom} » est un trésor culturel indispensable de la ville. "
                         . "Il se trouve au cœur de votre zone d'exploration actuelle. "
                         . "Rendez-vous au point précis indiqué sur votre carte pour valider votre présence physique.",
                'reponse' => 'valider',
                'solution' => "Excellent, vous avez localisé le repère de {$lieu->nom}.",
                'points' => 50,
            ],
            'force1' => [
                'titre' => 'En Route !',
                'texte' => "Vous cherchez actuellement : {$lieu->nom}.\n"
                         . "C'est un point de passage obligatoire pour la suite de votre parcours.\n"
                         . "Suivez les coordonnées de votre GPS. "
                         . "Activez le bouton « Valider sur place » dès que vous y êtes !",
                'reponse' => 'oui',
                'solution' => "Votre présence a été validée avec succès à {$lieu->nom}.",
                'points' => 30,
            ],
            'enfant' => [
                'titre' => 'Mission Petit Explorateur',
                'texte' => "🗺️ Mission explorateur !\n\n"
                         . "Tu es sur la piste d'un endroit très chouette qui s'appelle « {$lieu->nom} ».\n"
                         . "Demande à un adulte de t'aider à suivre la boussole sur l'écran.\n"
                         . "Dès que tu es arrivé tout près du monument, appuie sur le gros bouton vert ! 🎉",
                'reponse' => 'vert',
                'solution' => "Génial ! Tu as déniché {$lieu->nom} comme un vrai aventurier !",
                'points' => 100,
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
                    'actif' => true,
                    'image_url' => null,
                ]
            );
        }
    }
}