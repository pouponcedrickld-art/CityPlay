<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Modèle représentant un Utilisateur (Joueur ou Admin)
 *
 * Ce modèle gère l'authentification, les rôles (via Spatie),
 * et la progression globale du joueur.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Attributs assignables en masse.
     * Inclut les consentements RGPD et les données OTP.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pseudo',
        'consent_cgu',
        'consent_donnees',
        'otp_code',
        'otp_verified_at',
        'is_admin',
        'keep_account',
        'total_score',
    ];

    /**
     * Attributs cachés pour les tableaux/JSON.
     * On cache le mot de passe et le code OTP pour la sécurité.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    /**
     * Conversion automatique des types de données (Casting).
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'consent_cgu' => 'boolean',
        'consent_donnees' => 'boolean',
        'is_admin' => 'boolean',
        'keep_account' => 'boolean',
        'password' => 'hashed',
    ];

    /**
     * Équipes auxquelles appartient l'utilisateur.
     * Relation Many-to-Many avec pivot pour le rôle (Challenger/Participant).
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Équipes créées par cet utilisateur.
     */
    public function createdTeams()
    {
        return $this->hasMany(Team::class, 'createur_id');
    }

    /**
     * Parties (sessions de jeu) lancées par cet utilisateur.
     */
    public function parties()
    {
        return $this->hasMany(Partie::class, 'createur_id');
    }

    /**
     * Récupère la liste des IDs de tous les lieux déjà découverts.
     * Utile pour la carte globale et les succès.
     *
     * @return array
     */
    public function getLieuxDecouvertsIds(): array
    {
        return ProgressionPartie::whereIn('partie_id', $this->parties()->pluck('id'))
            ->get()
            ->pluck('lieux_decouverts')
            ->flatten()
            ->unique()
            ->filter()
            ->toArray();
    }
}
