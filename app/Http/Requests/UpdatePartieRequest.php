<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request pour la mise à jour d'une partie
 * 
 * Valide les données et vérifie les autorisations.
 * Utilisé pour bloquer les modifications si la partie est verrouillée.
 */
class UpdatePartieRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête
     * 
     * Vérifie que :
     * 1. L'utilisateur est authentifié
     * 2. C'est le créateur de la partie
     * 3. La partie n'est pas verrouillée
     */
    public function authorize(): bool
    {
        $partie = $this->route('partie');

        // Vérifie que la partie n'est pas verrouillée
        if ($partie->estVerrouillee()) {
            return false;
        }

        // Vérifie que l'utilisateur est le créateur
        return $this->user()->id === $partie->createur_id;
    }

    /**
     * Règles de validation des données
     */
    public function rules(): array
    {
        return [
            'parametres' => 'sometimes|array',
            'parametres.duree' => 'sometimes|integer|min:15|max:300',
            'parametres.locomotion' => 'sometimes|string|in:pied,velo,voiture,moto',
            'parametres.difficulte' => 'sometimes|integer|in:1,2,3',
            'parametres.nb_joueurs' => 'sometimes|integer|min:1|max:9',
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            'parametres.duree.min' => 'La durée minimale est de 15 minutes.',
            'parametres.duree.max' => 'La durée maximale est de 5 heures.',
            'parametres.locomotion.in' => 'Le mode de locomotion doit être : à pied, vélo, voiture ou moto.',
            'parametres.difficulte.in' => 'La difficulté doit être 1 (facile), 2 (moyen) ou 3 (difficile).',
            'parametres.nb_joueurs.max' => 'Le nombre de joueurs ne peut pas dépasser 9.',
        ];
    }

    /**
     * Réponse en cas d'autorisation refusée
     */
    protected function failedAuthorization()
    {
        $partie = $this->route('partie');

        if ($partie->estVerrouillee()) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Les paramètres de cette partie sont verrouillés et ne peuvent plus être modifiés.'
            );
        }

        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Vous n\'êtes pas autorisé à modifier cette partie.'
        );
    }
}