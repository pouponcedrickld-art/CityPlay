<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enigme extends Model
{
    use HasFactory;

    protected $fillable = [
        'lieu_id',
        'type',
        'titre',
        'texte',
        'reponse',
        'points',
        'image_url',
        'solution',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function lieu()
    {
        return $this->belongsTo(Lieu::class);
    }
}
