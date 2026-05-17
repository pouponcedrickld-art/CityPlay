<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'pseudo',
        'consent_cgu',
        'consent_donnees',
        'otp_code',
        'otp_verified_at',
        'is_admin',
        'keep_account',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'consent_cgu' => 'boolean',
        'consent_donnees' => 'boolean',
        'is_admin' => 'boolean',
        'keep_account' => 'boolean',
        'password' => 'hashed',
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function createdTeams()
    {
        return $this->hasMany(Team::class, 'createur_id');
    }

    public function parties()
    {
        return $this->hasMany(Partie::class, 'createur_id');
    }

    public function progressions()
    {
        return $this->hasMany(Progression::class);
    }
}
