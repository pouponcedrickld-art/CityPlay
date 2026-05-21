<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoLieu extends Model
{
    use HasFactory;

    protected $table = 'photo_lieus'; // ← forcer le nom

    protected $fillable = ['lieu_id', 'url', 'alt_text', 'ordre'];

    protected $appends = ['full_url'];

    public function getFullUrlAttribute(): string
    {
        if (filter_var($this->url, FILTER_VALIDATE_URL)) {
            return $this->url;
        }
        return asset('storage/' . $this->url);
    }

    public function lieu()
    {
        return $this->belongsTo(Lieu::class);
    }
}