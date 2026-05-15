<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoLieu extends Model
{
    use HasFactory;

    protected $fillable = ['lieu_id', 'url', 'alt_text', 'ordre'];

    public function lieu()
    {
        return $this->belongsTo(Lieu::class);
    }
}