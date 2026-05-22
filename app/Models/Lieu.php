<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un Lieu géographique (Point d'Intérêt).
 * 
 * Un lieu appartient à un environnement et contient plusieurs énigmes.
 * Il est défini par ses coordonnées GPS et son rayon de validation.
 */
class Lieu extends Model
{
    use HasFactory;

    /**
     * 🔥 Important : on force le nom de la table car "lieux" est l'irregulier de "lieu"
     */
    protected $table = 'lieux';

    /**
     * Attributs assignables en masse.
     */
    protected $fillable = [
        'environnement_id',
        'ordre',
        'actif',
        'latitude',
        'longitude',
        'rayon_metres',
        'nom',
        'description',
        'photos',
    ];

    /**
     * Conversion automatique des types.
     * photos est stocké en JSON et casté en array PHP.
     */
    protected $casts = [
        'actif'        => 'boolean',
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
        'rayon_metres' => 'integer',
        'ordre'        => 'integer',
        'photos'       => 'array',
    ];

    /**
     * L'environnement auquel appartient ce lieu.
     */
    public function environnement()
    {
        return $this->belongsTo(Environnement::class);
    }

    /**
     * Photos associées à ce lieu (via table pivot ou relation directe).
     */
    public function photos()
    {
        return $this->hasMany(PhotoLieu::class);
    }

    /**
     * Énigmes disponibles pour ce lieu.
     * Un lieu a généralement 4 énigmes (Force 1, 2, 3 et Enfant).
     */
    public function enigmes()
    {
        return $this->hasMany(Enigme::class);
    }

    /**
     * Récupère l'énigme spécifique pour un niveau de difficulté donné.
     */
    public function enigmeParType(string $type): ?Enigme
    {
        return $this->enigmes()->where('type', $type)->first();
    }
}
