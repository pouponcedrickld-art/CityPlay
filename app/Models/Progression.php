<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progression extends Model
{
    use HasFactory;

    protected $fillable = [
        'partie_id',
        'user_id',
        'lieux_a_visiter',
        'lieux_decouverts',
        'lieux_restants',
        'enigme_courante_id',
        'temps_restant_minutes',
        'nb_enigmes_resolues',
        'score'
    ];

    protected $casts = [
        'lieux_a_visiter' => 'array',
        'lieux_decouverts' => 'array',
        'lieux_restants' => 'array',
        'nb_enigmes_resolues' => 'integer',
        'score' => 'integer',
        'temps_restant_minutes' => 'integer',
    ];

    public function partie()
    {
        return $this->belongsTo(Partie::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enigmeCourante()
    {
        return $this->belongsTo(Enigme::class, 'enigme_courante_id');
    }
}