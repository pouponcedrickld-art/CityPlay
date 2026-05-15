<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enigme extends Model
{
    use HasFactory;

    public const TYPES = ['force1', 'force2', 'force3', 'enfant'];
     public const TYPE_LABELS = [
        'force3' => 'Difficile (Force 3)',
        'force2' => 'Intermédiaire (Force 2)',
        'force1' => 'Facile (Force 1)',
        'enfant' => 'Enfant',
    ];
    protected $fillable = ['lieu_id', 'type', 'texte', 'image_url', 'actif'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function lieu()
    {
        return $this->belongsTo(Lieu::class);
    }
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }
}
