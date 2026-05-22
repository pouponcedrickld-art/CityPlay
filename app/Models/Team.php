<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description', 'max_joueurs', 'code_liaison', 'createur_id', 'environnement_id'];

    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function environnement()
    {
        return $this->belongsTo(Environnement::class);
    }

    public function parties()
    {
        return $this->hasMany(Partie::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    public function messages()
    {
        return $this->hasMany(TeamMessage::class);
    }
}