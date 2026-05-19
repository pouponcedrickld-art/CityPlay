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
        'actif',
        'retention_profils_jours',
        'regles',
        'duree_vie_lien_heures',
        'message_bonne_reponse',
        'message_mauvaise_reponse',
        'message_fin',
        'message_pause',
        'lien_restauration',
        'lien_boutique',
        'lien_notation',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'retention_profils_jours' => 'integer',
        'duree_vie_lien_heures'   => 'integer',
    ];

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    public function lieux()
    {
        return $this->hasMany(Lieu::class)->orderBy('ordre');
    }

    public function parties()
    {
        return $this->hasMany(Partie::class);
    }
}
