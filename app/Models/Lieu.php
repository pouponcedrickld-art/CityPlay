<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lieu extends Model
{
    use HasFactory;

    protected $fillable = [
        'environnement_id',
        'ordre',
        'nom',
        'latitude',
        'longitude',
        'rayon_metres',
        'description',
        'actif'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'actif' => 'boolean',
    ];

    public function environnement()
    {
        return $this->belongsTo(Environnement::class);
    }

    public function photos()
    {
        return $this->hasMany(PhotoLieu::class);
    }

    public function enigmes()
    {
        return $this->hasMany(Enigme::class);
    }
}