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
        'expires_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];
}
