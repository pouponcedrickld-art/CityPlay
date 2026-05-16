<?php

namespace Database\Seeders;

use App\Models\Enigme;
use App\Models\Lieu;
use Illuminate\Database\Seeder;

/**
 * EnigmeSeeder
 * 
 * Crée 4 énigmes par lieu (force 1, 2, 3 + enfant) = 20 énigmes total.
 */
class EnigmeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lieux = Lieu::all();

        foreach ($lieux as $lieu) {
            $enigmes = $this->getEnigmesPourLieu($lieu->nom);
            
            foreach ($enigmes as $enigme) {
                Enigme::create(array_merge($enigme, [
                    'lieu_id' => $lieu->id,
                ]));
            }
        }

        $this->command->info('20 énigmes créées (4 par lieu).');
    }

    /**
     * Retourne les 4 énigmes pour un lieu donné.
     */
    private function getEnigmesPourLieu(string $nomLieu): array
    {
        $enigmes = [
            'Tour Eiffel' => [
                ['type' => 'force3', 'texte' => 'Je suis une dame de fer qui a dominé Paris depuis 1889. Qui suis-je ?', 'image_url' => 'tour_eiffel_force3.jpg'],
                ['type' => 'force2', 'texte' => 'Mon créateur s\'appelle Gustave. Je mesure 330 mètres. Qui suis-je ?', 'image_url' => 'tour_eiffel_force2.jpg'],
                ['type' => 'force1', 'texte' => 'Je suis le monument le plus célèbre de Paris, avec une vue à 360°. Qui suis-je ?', 'image_url' => 'tour_eiffel_force1.jpg'],
                ['type' => 'enfant', 'texte' => 'Je suis une grande tour en fer où on peut monter tout en haut. Qui suis-je ?', 'image_url' => 'tour_eiffel_enfant.jpg'],
            ],
            'Louvre' => [
                ['type' => 'force3', 'texte' => 'J\'étais un palais royal avant de devenir le plus grand musée d\'art du monde. Qui suis-je ?', 'image_url' => 'louvre_force3.jpg'],
                ['type' => 'force2', 'texte' => 'La Joconde me visite depuis 1797. Ma pyramide est en verre. Qui suis-je ?', 'image_url' => 'louvre_force2.jpg'],
                ['type' => 'force1', 'texte' => 'Je suis un grand musée à Paris avec une pyramide en verre à l\'entrée. Qui suis-je ?', 'image_url' => 'louvre_force1.jpg'],
                ['type' => 'enfant', 'texte' => 'On peut voir des tableaux très vieux chez moi, dont une dame qui sourit. Qui suis-je ?', 'image_url' => 'louvre_enfant.jpg'],
            ],
            'Notre-Dame' => [
                ['type' => 'force3', 'texte' => 'Je suis une cathédrale gothique du XIIe siècle, cœur historique de Paris sur une île. Qui suis-je ?', 'image_url' => 'notre_dame_force3.jpg'],
                ['type' => 'force2', 'texte' => 'Victor Hugo m\'a rendu célèbre avec un bossu et une gitane. Je suis sur l\'Île de la Cité. Qui suis-je ?', 'image_url' => 'notre_dame_force2.jpg'],
                ['type' => 'force1', 'texte' => 'Je suis une très vieille église de Paris avec des gargouilles sur le toit. Qui suis-je ?', 'image_url' => 'notre_dame_force1.jpg'],
                ['type' => 'enfant', 'texte' => 'Je suis une grande église où il y a des monstres en pierre qui regardent en bas. Qui suis-je ?', 'image_url' => 'notre_dame_enfant.jpg'],
            ],
            'Sacré-Cœur' => [
                ['type' => 'force3', 'texte' => 'Je suis une basilique blanche au sommet de la butte Montmartre, dédiée au Sacré-Cœur de Jésus. Qui suis-je ?', 'image_url' => 'sacre_coeur_force3.jpg'],
                ['type' => 'force2', 'texte' => 'On peut me voir de partout à Paris car je suis tout en haut d\'une colline. Je suis blanche. Qui suis-je ?', 'image_url' => 'sacre_coeur_force2.jpg'],
                ['type' => 'force1', 'texte' => 'Je suis une belle église blanche au sommet de Montmartre avec une super vue. Qui suis-je ?', 'image_url' => 'sacre_coeur_force1.jpg'],
                ['type' => 'enfant', 'texte' => 'Je suis une maison blanche tout en haut d\'une colline où on voit tout Paris. Qui suis-je ?', 'image_url' => 'sacre_coeur_enfant.jpg'],
            ],
            'Panthéon' => [
                ['type' => 'force3', 'texte' => 'Je suis un mausolée pour les "Grands Hommes" de la République française. J\'ai une coupole immense. Qui suis-je ?', 'image_url' => 'pantheon_force3.jpg'],
                ['type' => 'force2', 'texte' => 'Voltaire, Rousseau et Marie Curie reposent en moi. J\'étais une église avant. Qui suis-je ?', 'image_url' => 'pantheon_force2.jpg'],
                ['type' => 'force1', 'texte' => 'Je suis un grand bâtiment avec un dôme où on met les personnes très importantes de France. Qui suis-je ?', 'image_url' => 'pantheon_force1.jpg'],
                ['type' => 'enfant', 'texte' => 'Je suis une grande maison ronde où on garde les héros de France. Qui suis-je ?', 'image_url' => 'pantheon_enfant.jpg'],
            ],
        ];

        return $enigmes[$nomLieu] ?? [];
    }
}
