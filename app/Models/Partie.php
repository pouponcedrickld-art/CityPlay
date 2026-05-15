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
        'statut',
        'code_liaison',
        'expire_at',
        'started_at',
        'ended_at'
    ];

    protected $casts = [
        'parametres' => 'array',
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
}