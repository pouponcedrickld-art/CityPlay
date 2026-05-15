<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lieu extends Model
{
    use HasFactory;

    // 🔥 Important : on force le nom de la table
    protected $table = 'lieux';

  protected $fillable = [
        'environnement_id',
        'ordre',
        'latitude',
        'longitude',
        'rayon_metres',
        'nom',
        'description',
        'photos',
    ];

    protected $casts = [
        'latitude'     => 'decimal:7',
        'longitude'    => 'decimal:7',
        'rayon_metres' => 'integer',
        'ordre'        => 'integer',
        'photos'       => 'array', // JSON auto-cast
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
    public function enigmeParType(string $type): ?Enigme
    {
        return $this->enigmes()->where('type', $type)->first();
    }
}
