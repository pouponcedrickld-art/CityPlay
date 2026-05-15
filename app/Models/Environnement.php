<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Environnement extends Model
{
    use HasFactory;

    protected $fillable = [
        'ville_id',
        'nom',
        'retention_profils_jours',
        'duree_vie_lien_heures',
        'messages',
        'regles',
        'actif'
    ];

    protected $casts = [
        'messages' => 'array',
        'regles' => 'array',
        'actif' => 'boolean',
    ];

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    public function lieux()
    {
        return $this->hasMany(Lieu::class);
    }

    public function parties()
    {
        return $this->hasMany(Partie::class);
    }
}