<?php

namespace App\Services;

use App\Models\Partie;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Service de gestion des invitations
 * 
 * Responsable de :
 * - Générer des codes uniques de liaison
 * - Créer les liens de partage
 * - Gérer l'expiration (TTL)
 * - Formater les codes pour un affichage lisible
 */
class InvitationService
{
    /**
     * Longueur du code de liaison (sans le tiret)
     * Ex: 8 caractères = "ABCD-1234"
     */
    private const CODE_LENGTH = 8;

    /**
     * Nombre de tentatives max pour générer un code unique
     * Évite une boucle infinie si tous les codes sont pris
     */
    private const MAX_ATTEMPTS = 10;

    /**
     * Génère une invitation complète pour une partie
     * 
     * @param Partie $partie La partie à partager
     * @param int $ttlHours Durée de vie en heures (récupérée des paramètres admin)
     * @return Partie La partie mise à jour avec code et lien
     * @throws \Exception Si impossible de générer un code unique
     */
    public function genererInvitation(Partie $partie, int $ttlHours = 24): Partie
    {
        // Génère un code unique (vérifie qu'il n'existe pas déjà)
        $code = $this->genererCodeUnique();

        // Calcule la date d'expiration basée sur le TTL
        $expiration = Carbon::now()->addHours($ttlHours);

        // Met à jour la partie
        $partie->code_liaison = $code;
        $partie->expires_at = $expiration;
        $partie->verrouillee = true; // Verrouille les paramètres au moment du partage
        $partie->save();

        // Génère le lien complet après sauvegarde (besoin de l'ID)
        $partie->lien_partage = $partie->genererLienPartage();
        $partie->save();

        return $partie;
    }

    /**
     * Génère un code alphanumérique unique
     * Format : XXXX-XXXX (4 lettres/chiffres - 4 lettres/chiffres)
     * Facile à lire et à saisir manuellement
     * 
     * @return string Le code généré
     * @throws \Exception Après MAX_ATTEMPTS échouées
     */
    private function genererCodeUnique(): string
    {
        $attempts = 0;

        do {
            // Génère 8 caractères alphanumériques majuscules
            $code = strtoupper(Str::random(self::CODE_LENGTH));

            // Formate avec un tiret au milieu pour lisibilité
            $codeFormate = substr($code, 0, 4) . '-' . substr($code, 4, 4);

            // Vérifie si ce code existe déjà en BDD
            $existe = Partie::where('code_liaison', $codeFormate)->exists();

            $attempts++;

            // Si le code n'existe pas, on le retourne
            if (!$existe) {
                return $codeFormate;
            }

        } while ($attempts < self::MAX_ATTEMPTS);

        // Si toutes les tentatives échouent (extrêmement rare), on ajoute un suffixe aléatoire plus long
        return substr(strtoupper(Str::random(10)), 0, 4) . '-' . substr(strtoupper(Str::random(10)), 0, 4);
    }

    /**
     * Régénère une invitation (si l'ancienne a expiré ou été perdue)
     * 
     * @param Partie $partie La partie concernée
     * @param int $ttlHours Nouvelle durée de vie
     * @return Partie La partie avec nouvelle invitation
     */
    public function regenererInvitation(Partie $partie, int $ttlHours = 24): Partie
    {
        // Invalide l'ancien code en mettant expires_at à maintenant
        $partie->expires_at = Carbon::now();
        $partie->save();

        // Génère une nouvelle invitation
        return $this->genererInvitation($partie, $ttlHours);
    }

    /**
     * Vérifie si un code de liaison est valide
     * 
     * @param string $code Le code saisi par le joueur
     * @return Partie|null La partie associée ou null si invalide
     */
    public function trouverParCode(string $code): ?Partie
    {
        $partie = Partie::where('code_liaison', $code)->first();

        if (!$partie) {
            return null;
        }

        // Vérifie si l'invitation n'a pas expiré
        if ($partie->estExpiree()) {
            return null;
        }

        return $partie;
    }

    /**
     * Formate un code pour l'affichage (ajoute espaces si besoin)
     * Ex: "ABCD-1234" -> "ABCD - 1234"
     */
    public static function formaterPourAffichage(string $code): string
    {
        return implode(' - ', explode('-', $code));
    }
}