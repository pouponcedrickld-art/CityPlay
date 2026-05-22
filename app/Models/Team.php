<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une Équipe de joueurs.
 *
 * Une équipe est liée à un environnement spécifique et peut participer
 * à plusieurs parties (sessions de jeu).
 */
class Team extends Model
{
    use HasFactory;

    /**
     * Attributs assignables en masse.
     * code_liaison permet de rejoindre l'équipe via un lien unique.
     */
    protected $fillable = ['nom', 'description', 'max_joueurs', 'code_liaison', 'createur_id', 'environnement_id'];

    /**
     * Le créateur (Challenger) de l'équipe.
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    /**
     * L'environnement de jeu choisi pour cette équipe.
     */
    public function environnement()
    {
        return $this->belongsTo(Environnement::class);
    }

    /**
     * Historique des parties jouées par cette équipe.
     */
    public function parties()
    {
        return $this->hasMany(Partie::class);
    }

    /**
     * Membres de l'équipe (Joueurs).
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    /**
     * Messages échangés dans le chat de l'équipe.
     */
    public function messages()
    {
        return $this->hasMany(TeamMessage::class);
    }
}
