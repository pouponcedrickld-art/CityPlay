<?php

namespace Database\Seeders;

use App\Models\Ville;
use App\Models\Environnement;
use App\Models\Lieu;
use App\Models\Enigme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        // 2. Créer un environnement (Parcours)
        $environnement = Environnement::updateOrCreate(
            ['nom' => 'Les Trésors de Cotonou', 'ville_id' => $ville->id],
            [
                'actif' => true,
                'retention_profils_jours' => 30,
                'duree_vie_lien_heures' => 24,
                'message_bonne_reponse' => 'Excellent ! Vous avez l\'œil d\'un véritable explorateur.',
                'message_mauvaise_reponse' => 'Ce n\'est pas tout à fait ça... Regardez mieux autour de vous.',
                'message_fin' => 'Félicitations ! Vous avez terminé le parcours de Cotonou.',
                'message_pause' => 'Partie en pause. Revenez vite pour la suite !',
            ]
        );

        // 3. Créer des Lieux et Énigmes
        
        // Lieu 1 : Place de l'Étoile Rouge
        $lieu1 = Lieu::updateOrCreate(
            ['nom' => 'Place de l\'Étoile Rouge', 'environnement_id' => $environnement->id],
            [
                'ordre' => 1,
                'actif' => true,
                'latitude' => 6.370500,
                'longitude' => 2.417200,
                'rayon_metres' => 50,
                'description' => 'Un monument emblématique symbolisant l\'histoire politique du Bénin.',
            ]
        );

        Enigme::updateOrCreate(
            ['lieu_id' => $lieu1->id, 'type' => 'force2'],
            [
                'titre' => 'Le Monument Central',
                'texte' => 'Combien de branches compte l\'étoile au sommet du monument central de cette place ?',
                'reponse' => '5',
                'solution' => 'Il y a 5 branches sur l\'étoile rouge.',
                'points' => 50,
                'actif' => true,
            ]
        );

        // Lieu 2 : Fondation Zinsou
        $lieu2 = Lieu::updateOrCreate(
            ['nom' => 'Fondation Zinsou', 'environnement_id' => $environnement->id],
            [
                'ordre' => 2,
                'actif' => true,
                'latitude' => 6.353300,
                'longitude' => 2.428800,
                'rayon_metres' => 30,
                'description' => 'Un espace dédié à l\'art contemporain africain dans un bâtiment historique.',
            ]
        );

        Enigme::updateOrCreate(
            ['lieu_id' => $lieu2->id, 'type' => 'force2'],
            [
                'titre' => 'Art et Histoire',
                'texte' => 'Quel animal sculpté accueille souvent les visiteurs à l\'entrée de ce centre d\'art ?',
                'reponse' => 'lion',
                'solution' => 'Le lion est le symbole de la fondation.',
                'points' => 70,
                'actif' => true,
            ]
        );

        // Lieu 3 : Marché Dantokpa
        $lieu3 = Lieu::updateOrCreate(
            ['nom' => 'Marché Dantokpa', 'environnement_id' => $environnement->id],
            [
                'ordre' => 3,
                'actif' => true,
                'latitude' => 6.372500,
                'longitude' => 2.436100,
                'rayon_metres' => 100,
                'description' => 'Le plus grand marché à ciel ouvert de l\'Afrique de l\'Ouest.',
            ]
        );

        Enigme::updateOrCreate(
            ['lieu_id' => $lieu3->id, 'type' => 'force3'],
            [
                'titre' => 'Le Cœur du Marché',
                'texte' => 'Près de quelle lagune se situe ce marché mythique ?',
                'reponse' => 'nokoue',
                'solution' => 'La lagune de Nokoué borde le marché.',
                'points' => 100,
                'actif' => true,
            ]
        );
    }
}
