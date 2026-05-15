<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modèle Partie
 * 
 * Représente une session de jeu créée par un joueur.
 * Contient les paramètres figés et les informations de partage.
 */
class Partie extends Model
{
    use HasFactory;

    /**
     * Attributs fillables pour mass assignment
     */
    protected $fillable = [
        'environnement_id',  // Ville/environnement choisi
        'createur_id',       // Joueur qui a créé la partie
        'team_id',           // Équipe associée (null si solo)
        'mode',              // 'solo' ou 'team'
        'parametres',        // JSON : durée, locomotion, difficulté, nb_joueurs
        'statut',            // en_attente, en_cours, terminee, abandonnee
        'code_liaison',      // Code de partage
        'lien_partage',      // URL de partage complète
        'expires_at',        // Date d'expiration du lien
        'verrouillee',       // true = paramètres figés
        'started_at',        // Début de la partie
        'ended_at',          // Fin de la partie
    ];

    /**
     * Cast des attributs
     */
    protected $casts = [
        'parametres' => 'array',      // JSON -> tableau PHP
        'verrouillee' => 'boolean',    // TINYINT -> bool
        'expires_at' => 'datetime',   // Timestamp -> Carbon
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Relation : la partie appartient à un environnement
     */
    public function environnement(): BelongsTo
    {
        return $this->belongsTo(Environnement::class);
    }

    /**
     * Relation : la partie a un créateur (utilisateur)
     */
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    /**
     * Relation : la partie peut avoir une team
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Relation : une partie a une progression associée
     */
    public function progression(): HasOne
    {
        return $this->hasOne(ProgressionPartie::class);
    }

    /**
     * Vérifie si les paramètres sont verrouillés
     * Si true, plus aucune modification autorisée
     */
    public function estVerrouillee(): bool
    {
        return $this->verrouillee === true;
    }

    /**
     * Vérifie si le code/lien a expiré
     * Compare expires_at avec l'heure actuelle
     */
    public function estExpiree(): bool
    {
        if (is_null($this->expires_at)) {
            return false; // Pas d'expiration définie = jamais expiré
        }
        return $this->expires_at->isPast();
    }

    /**
     * Génère le lien de partage complet
     * Basé sur l'URL de l'application + code de liaison
     */
    public function genererLienPartage(): string
    {
        $baseUrl = config('app.url'); // URL définie dans .env APP_URL
        return "{$baseUrl}/rejoindre/{$this->code_liaison}";
    }

    /**
     * Vérifie si la partie est en cours
     */
    public function estEnCours(): bool
    {
        return $this->statut === 'en_cours';
    }

    /**
     * Vérifie si la partie est terminée
     */
    public function estTerminee(): bool
    {
        return in_array($this->statut, ['terminee', 'abandonnee']);
    }
}