<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partie extends Model
{
    use HasFactory;

    protected $fillable = [
        'environnement_id',
        'createur_id',
        'team_id',
        'mode',
        'parametres',
        'score_total',
        'statut',
        'code_liaison',
        'expire_at',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'parametres' => 'array',
        'score_total' => 'integer',
        'expire_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function environnement()
    {
        return $this->belongsTo(Environnement::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function progressions()
    {
        return $this->hasMany(Progression::class);
    }

    /**
     * Retourne la progression de l'utilisateur actuel pour cette partie.
     */
    public function progression()
    {
        return $this->hasOne(Progression::class)->where('user_id', auth()->id());
    }
}