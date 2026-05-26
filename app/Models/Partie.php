<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une Session de Jeu (Partie).
 * 
 * C'est l'élément central qui lie une équipe, un environnement et une progression.
 * Gère les statuts (en cours, terminée, pause), le score et le temps.
 */
class Partie extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse.
     * code_liaison et lien_partage permettent d'inviter d'autres joueurs.
     */
    protected $fillable = [
        'environnement_id',
        'createur_id',
        'team_id',
        'mode',
        'ordre_jeu',
        'lat_depart',
        'lng_depart',
        'parametres',
        'statut',
        'code_liaison',
        'lien_partage',
        'expire_at',
        'verrouillee',
        'started_at',
        'ended_at',
        'score_total',
    ];

    /**
     * Le créateur de la session de jeu.
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    /**
     * L'environnement (ville/quartier) où se déroule la partie.
     */
    public function environnement()
    {
        return $this->belongsTo(Environnement::class);
    }

    /**
     * L'équipe qui participe à cette partie.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * La progression détaillée (étapes, énigmes résolues, temps par étape).
     */
    public function progression()
    {
        return $this->hasOne(ProgressionPartie::class);
    }

    /**
     * Cast des attributs pour manipuler des objets PHP (Carbon, array).
     */
    protected $casts = [
        'parametres' => 'array',
        'verrouillee' => 'boolean',
        'expire_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Retourne ou génère le lien d'invitation pour rejoindre la partie.
     * 
     * @return string|null
     */
    public function getLienInvitation(): ?string
    {
        if (!empty($this->lien_partage)) {
            return $this->lien_partage;
        }

        return $this->genererLienPartage();
    }

    /**
     * Génère l'URL complète basée sur le code de liaison unique.
     */
    public function genererLienPartage(): ?string
    {
        if (blank($this->code_liaison)) {
            return null;
        }

        return route('parties.rejoindre', $this->code_liaison);
    }

    /**
     * Vérifie si la session de jeu a expiré (temps limite dépassé).
     */
    public function estExpiree(): bool
    {
        return $this->expire_at && $this->expire_at->isPast();
    }

    /**
     * Vérifie si la partie est verrouillée (plus de nouveaux joueurs possibles).
     */
    public function estVerrouillee(): bool
    {
        return (bool) $this->verrouillee;
    }
}
