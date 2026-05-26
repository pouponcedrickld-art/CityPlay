<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamJoinRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class TeamJoinRequestController extends Controller
{
    /**
     * Liste des équipes publiques disponibles pour adhésion.
     */
    public function discover()
    {
        $user = Auth::user();
        
        // On récupère les équipes où l'utilisateur n'est pas déjà membre
        // et pour lesquelles il n'a pas de demande en attente.
        $teams = Team::whereDoesntHave('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->whereDoesntHave('joinRequests', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('status', 'pending');
        })
        ->with('createur')
        ->withCount('users')
        ->get();

        return Inertia::render('Teams/Discover', [
            'teams' => $teams
        ]);
    }

    /**
     * Envoyer une demande d'adhésion à une équipe.
     */
    public function store(Team $team)
    {
        $user = Auth::user();

        // Vérifier si déjà membre
        if ($team->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Vous êtes déjà membre de cette équipe.');
        }

        // Vérifier si demande déjà en cours
        if (TeamJoinRequest::where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->where('status', 'pending')
            ->exists()) {
            return back()->with('error', 'Une demande est déjà en cours pour cette équipe.');
        }

        TeamJoinRequest::create([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Votre demande d\'adhésion a été envoyée au chef d\'équipe.');
    }

    /**
     * Liste des demandes reçues par le créateur de l'équipe.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Demandes pour les équipes créées par l'utilisateur
        $requests = TeamJoinRequest::whereHas('team', function ($query) use ($user) {
            $query->where('createur_id', $user->id);
        })
        ->where('status', 'pending')
        ->with(['user', 'team'])
        ->get();

        return Inertia::render('Teams/Requests', [
            'requests' => $requests
        ]);
    }

    /**
     * Accepter une demande d'adhésion.
     */
    public function accept(TeamJoinRequest $request)
    {
        // Vérifier que c'est bien le créateur de l'équipe
        if ($request->team->createur_id !== Auth::id()) {
            abort(403);
        }

        // Ajouter l'utilisateur à l'équipe
        $request->team->users()->attach($request->user_id, ['role' => 'participant']);

        // Mettre à jour le statut de la demande
        $request->update(['status' => 'accepted']);

        return back()->with('success', 'Utilisateur ajouté à l\'équipe.');
    }

    /**
     * Rejeter une demande d'adhésion.
     */
    public function reject(TeamJoinRequest $request)
    {
        // Vérifier que c'est bien le créateur de l'équipe
        if ($request->team->createur_id !== Auth::id()) {
            abort(403);
        }

        $request->update(['status' => 'rejected']);

        return back()->with('success', 'Demande rejetée.');
    }
}
