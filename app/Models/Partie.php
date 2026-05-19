<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partie extends Model
{
    use HasFactory;

    /**
     * Attributs fillables pour mass assignment
     */
    protected $fillable = [
        'environnement_id',
        'createur_id',
        'team_id',
        'mode',
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
     * Relations
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function environnement()
    {
        return $this->belongsTo(Environnement::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function progression()
    {
        return $this->hasOne(ProgressionPartie::class);
    }

    /**
     * Cast des attributs
     */
    protected $casts = [
        'parametres' => 'array',
        'verrouillee' => 'boolean',
        'expire_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Lien d'invitation pour rejoindre la partie (null si pas de code)
     */
    public function getLienInvitation(): ?string
    {
        if (!empty($this->lien_partage)) {
            return $this->lien_partage;
        }

        return $this->genererLienPartage();
    }

    public function genererLienPartage(): ?string
    {
        if (blank($this->code_liaison)) {
            return null;
        }

        return route('parties.rejoindre', $this->code_liaison);
    }

    public function estExpiree(): bool
    {
        return $this->expire_at && $this->expire_at->isPast();
    }

    public function estVerrouillee(): bool
    {
        return (bool) $this->verrouillee;
    }
}
