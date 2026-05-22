<?php

namespace App\Policies;

use App\Models\Partie;
use App\Models\User;

/**
 * Policy Partie
 *
 * Définit les règles d'accès aux parties :
 * - Seul le créateur peut modifier/supprimer
 * - Personne ne peut modifier une partie verrouillée
 * - Les membres peuvent voir mais pas modifier
 */
class PartiePolicy
{
    /**
     * Règle "before" : s'exécute avant toutes les autres vérifications.
     * Si elle retourne true, l'accès est accordé sans vérifier le reste.
     * Ici, on autorise l'administrateur à tout voir et tout faire sur n'importe quelle partie.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_admin || $user->hasRole('admin')) {
            return true;
        }
        return null; // On continue vers les autres méthodes pour les non-admins
    }

    /**
     * Détermine si l'utilisateur peut voir la liste des parties
     */
    public function viewAny(User $user): bool
    {
        return true; // Tout utilisateur authentifié peut voir ses parties
    }

    /**
     * Détermine si l'utilisateur peut voir une partie spécifique
     */
    public function view(User $user, Partie $partie): bool
    {
        // Le créateur peut toujours voir
        if ($user->id === $partie->createur_id) {
            return true;
        }

        // Les membres de la team peuvent voir (si mode team)
        if ($partie->mode === 'team' && $partie->team) {
            return $partie->team->membres->contains($user->id);
        }

        // Sinon, accès refusé
        return false;
    }

    /**
     * Détermine si l'utilisateur peut créer une partie
     */
    public function create(User $user): bool
    {
        return true; // Tout utilisateur authentifié peut créer
    }

    /**
     * Détermine si l'utilisateur peut modifier une partie
     *
     * RÈGLE CLÉ : interdit si verrouillée
     */
    public function update(User $user, Partie $partie): bool
    {
        // Interdit si la partie est verrouillée
        if ($partie->estVerrouillee()) {
            return false;
        }

        // Seul le créateur peut modifier
        return $user->id === $partie->createur_id;
    }

    /**
     * Détermine si l'utilisateur peut supprimer une partie
     */
    public function delete(User $user, Partie $partie): bool
    {
        // Seul le créateur peut supprimer
        return $user->id === $partie->createur_id;
    }

    /**
     * Détermine si l'utilisateur peut restaurer une partie supprimée
     */
    public function restore(User $user, Partie $partie): bool
    {
        return $user->id === $partie->createur_id;
    }

    /**
     * Détermine si l'utilisateur peut supprimer définitivement
     */
    public function forceDelete(User $user, Partie $partie): bool
    {
        return $user->id === $partie->createur_id;
    }

    /**
     * Détermine si l'utilisateur peut générer une invitation
     * Seul le créateur d'une partie non verrouillée peut le faire
     */
    public function genererInvitation(User $user, Partie $partie): bool
    {
        if ($partie->estVerrouillee()) {
            return false;
        }

        return $user->id === $partie->createur_id;
    }

    /**
     * Détermine si l'utilisateur peut rejoindre une partie
     */
    public function rejoindre(User $user, Partie $partie): bool
    {
        // Ne peut pas rejoindre sa propre partie
        if ($user->id === $partie->createur_id) {
            return false;
        }

        // Vérifie que l'invitation n'a pas expiré
        if ($partie->estExpiree()) {
            return false;
        }

        // Vérifie le nombre de joueurs max
        $nbJoueurs = $partie->parametres['nb_joueurs'] ?? 1;
        $nbActuels = $partie->team ? $partie->team->membres->count() : 1;

        return $nbActuels < $nbJoueurs;
    }
}
