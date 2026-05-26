<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('team.{teamId}', function ($user, $teamId) {
    return $user->teams()->where('team_id', $teamId)->exists();
});

Broadcast::channel('partie.{partieId}', function ($user, $partieId) {
    $partie = \App\Models\Partie::find($partieId);
    if (!$partie) return false;
    
    // Admin peut tout voir
    if ($user->is_admin) return true;
    
    // Le créateur peut voir
    if ($user->id === $partie->createur_id) return true;
    
    // Les membres de l'équipe peuvent voir
    if ($partie->team_id && $user->teams()->where('team_id', $partie->team_id)->exists()) {
        return true;
    }
    
    return false;
});
