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

        // 3. Liste de 15 Lieux pour Cotonou
        $lieux = [
            [
                'nom' => 'Place de l\'Étoile Rouge',
                'ordre' => 1,
                'latitude' => 6.370500,
                'longitude' => 2.417200,
                'rayon_metres' => 50,
                'description' => 'Un monument emblématique en briques rouges symbolisant l\'histoire politique du Bénin.',
            ],
            [
                'nom' => 'Esplanade de l\'Amazone',
                'ordre' => 2,
                'latitude' => 6.348600,
                'longitude' => 2.428100,
                'rayon_metres' => 60,
                'description' => 'Statue majestueuse de 30 mètres, symbole de la bravoure féminine béninoise.',
            ],
            [
                'nom' => 'Fondation Zinsou',
                'ordre' => 3,
                'latitude' => 6.353300,
                'longitude' => 2.428800,
                'rayon_metres' => 30,
                'description' => 'Espace d\'art contemporain dans une villa historique de style afro-brésilien.',
            ],
            [
                'nom' => 'Cathédrale Notre-Dame',
                'ordre' => 4,
                'latitude' => 6.353900,
                'longitude' => 2.433800,
                'rayon_metres' => 40,
                'description' => 'Architecture remarquable rayée de rouge et de blanc, près de l\'Ancien Pont.',
            ],
            [
                'nom' => 'Marché Dantokpa',
                'ordre' => 5,
                'latitude' => 6.372500,
                'longitude' => 2.436100,
                'rayon_metres' => 100,
                'description' => 'Le plus grand marché à ciel ouvert de l\'Afrique de l\'Ouest.',
            ],
            [
                'nom' => 'Ancien Pont de Cotonou',
                'ordre' => 6,
                'latitude' => 6.358200,
                'longitude' => 2.435500,
                'rayon_metres' => 50,
                'description' => 'Pont historique reliant les deux rives de la lagune de Cotonou.',
            ],
            [
                'nom' => 'Place des Martyrs',
                'ordre' => 7,
                'latitude' => 6.351200,
                'longitude' => 2.404800,
                'rayon_metres' => 45,
                'description' => 'Monument commémoratif dédié aux héros tombés pour la patrie.',
            ],
            [
                'nom' => 'Palais des Congrès',
                'ordre' => 8,
                'latitude' => 6.350800,
                'longitude' => 2.399500,
                'rayon_metres' => 55,
                'description' => 'Édifice imposant accueillant les grands événements nationaux.',
            ],
            [
                'nom' => 'Port Autonome de Cotonou',
                'ordre' => 9,
                'latitude' => 6.347500,
                'longitude' => 2.425000,
                'rayon_metres' => 80,
                'description' => 'Poumon économique du pays et point d\'entrée maritime majeur.',
            ],
            [
                'nom' => 'Stade de l\'Amitié Général Mathieu Kérékou',
                'ordre' => 10,
                'latitude' => 6.384500,
                'longitude' => 2.384200,
                'rayon_metres' => 90,
                'description' => 'Principal complexe sportif du Bénin.',
            ],
            [
                'nom' => 'Aéroport Cardinal Bernardin Gantin',
                'ordre' => 11,
                'latitude' => 6.357500,
                'longitude' => 2.385000,
                'rayon_metres' => 100,
                'description' => 'La porte d\'entrée aérienne du Bénin.',
            ],
            [
                'nom' => 'Centre Artisanal de Cotonou',
                'ordre' => 12,
                'latitude' => 6.354500,
                'longitude' => 2.421500,
                'rayon_metres' => 40,
                'description' => 'Lieu idéal pour découvrir l\'artisanat local et les souvenirs béninois.',
            ],
            [
                'nom' => 'La Haie Vive (Quartier gastronomique)',
                'ordre' => 13,
                'latitude' => 6.359000,
                'longitude' => 2.398000,
                'rayon_metres' => 60,
                'description' => 'Quartier animé réputé pour ses nombreux restaurants et sa vie nocturne.',
            ],
            [
                'nom' => 'Plage de Fidjrossè',
                'ordre' => 14,
                'latitude' => 6.345000,
                'longitude' => 2.365000,
                'rayon_metres' => 100,
                'description' => 'Zone balnéaire prisée pour la détente et les loisirs en bord de mer.',
            ],
            [
                'nom' => 'Ministère des Affaires Étrangères',
                'ordre' => 15,
                'latitude' => 6.349500,
                'longitude' => 2.397000,
                'rayon_metres' => 40,
                'description' => 'Édifice gouvernemental remarquable par son architecture moderne.',
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
                'titre' => 'Le Secret des Pierres',
                'texte' => "Je suis le gardien secret d'une histoire fascinante,\n"
                         . "Mon architecture et mes secrets attendent votre présence vigilante.\n"
                         . "Regardez autour de vous pour percer mes mystères les plus beaux,\n"
                         . "Ma silhouette se dresse fièrement pour guider le voyage de {$lieu->nom}.\n"
                         . "Quel est ce lieu historique ?",
                'reponse' => 'cotonou',
                'solution' => "Félicitations ! Vous avez décrypté l'histoire entourant {$lieu->nom}.",
                'points' => 70,
                'indice' => "Cherchez un indice visuel sur le monument ou à proximité.",
            ],
            'force2' => [
                'titre' => 'Repère Historique',
                'texte' => "Ce monument emblématique nommé « {$lieu->nom} » est un trésor culturel indispensable de la ville. "
                         . "Il se trouve au cœur de votre zone d'exploration actuelle. "
                         . "Rendez-vous au point précis indiqué sur votre carte pour valider votre présence physique.",
                'reponse' => 'valider',
                'solution' => "Excellent, vous avez localisé le repère de {$lieu->nom}.",
                'points' => 50,
                'indice' => "Le GPS est votre meilleur allié ici.",
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
                'indice' => "Avancez vers le marqueur sur la carte.",
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
                'indice' => "Regarde bien devant toi, tu y es presque !",
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
                    'image_url' => null,
                ]
            );
        }
    }
}
