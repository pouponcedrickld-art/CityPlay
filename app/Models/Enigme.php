<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une Énigme liée à un Lieu.
 *
 * Contient le texte de l'énigme, l'indice, la réponse attendue
 * et la solution détaillée affichée après réussite/échec.
 */
class Enigme extends Model
{
    use HasFactory;

    /**
     * Types de difficultés disponibles.
     */
    public const TYPES = ['force1', 'force2', 'force3', 'enfant'];

    /**
     * Libellés lisibles pour l'interface (Admin/Joueur).
     */
     public const TYPE_LABELS = [
        'force3' => 'Difficile (Force 3)',
        'force2' => 'Intermédiaire (Force 2)',
        'force1' => 'Facile (Force 1)',
        'enfant' => 'Enfant',
    ];

    /**
     * Attributs assignables en masse.
     */
    protected $fillable = ['lieu_id', 'type', 'titre', 'texte', 'indice', 'reponse', 'solution', 'points', 'image_url', 'actif'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    /**
     * Attributs virtuels ajoutés au JSON.
     */
    protected $appends = ['full_image_url'];

    /**
     * Accesseur pour obtenir l'URL complète de l'image.
     * Gère les URLs externes et les fichiers stockés localement.
     */
    public function getFullImageUrlAttribute(): ?string
    {
        if (!$this->image_url) {
            return null;
        }
        if (filter_var($this->image_url, FILTER_VALIDATE_URL)) {
            return $this->image_url;
        }
        return asset('storage/' . $this->image_url);
    }

    /**
     * Le lieu auquel cette énigme est rattachée.
     */
    public function lieu()
    {
        return $this->belongsTo(Lieu::class);
    }

    /**
     * Accesseur pour obtenir le libellé du type de difficulté.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }
}
