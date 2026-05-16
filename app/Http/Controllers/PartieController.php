<?php

namespace App\Http\Controllers;

use App\Models\Partie;
use App\Models\Environnement;
use App\Models\Progression;
use App\Services\PartieService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PartieController extends Controller
{
    protected $partieService;

    public function __construct(PartieService $partieService)
    {
        $this->partieService = $partieService;
    }

    /**
     * Affiche la liste des parties de l'utilisateur.
     * C'est le tableau de bord principal du joueur.
     */
    public function index()
    {
        $parties = Partie::where('createur_id', auth()->id())
            ->with(['environnement', 'progression'])
            ->latest()
            ->get();

        $environnements = Environnement::where('actif', true)->get();

        return Inertia::render('Player/Dashboard', [
            'parties' => $parties,
            'environnements' => $environnements
        ]);
    }

    /**
     * Affiche le formulaire de création d'une nouvelle partie.
     */
    public function create()
    {
        $environnements = Environnement::where('actif', true)
            ->with('lieux')
            ->get();

        return Inertia::render('Player/CreatePartie', [
            'environnements' => $environnements
        ]);
    }

    /**
     * Enregistre une nouvelle partie et initialise la progression.
     */
    public function store(Request $request)
    {
        $request->validate([
            'environnement_id' => 'required|exists:environnements,id',
            'mode' => 'required|in:solo,team',
            'duree_prevue' => 'required|integer|min:15|max:180',
            'locomotion' => 'required|string|in:pied,velo,voiture',
            'nb_joueurs' => 'nullable|integer|min:1|max:10',
        ]);

        $partie = $this->partieService->creerPartie($request->all(), auth()->id());

        // Si c'est une partie en équipe, on redirige vers le choix du rôle (ou partage du code)
        if ($partie->mode === 'team') {
            return redirect()->route('parties.team-setup', $partie->id);
        }

        return redirect()->route('progression.enigme', $partie->id)
            ->with('success', 'La partie a commencé ! Bonne chance.');
    }

    /**
     * Affiche la configuration de l'équipe pour une partie multi.
     */
    public function teamSetup(Partie $partie)
    {
        $partie->load('team.users');
        return Inertia::render('Player/JoinPartie', [
            'partie' => $partie
        ]);
    }

    /**
     * Met la partie en pause.
     */
    public function pause(Partie $partie)
    {
        $partie->update(['statut' => 'pause']);
        return back()->with('success', 'Partie mise en pause.');
    }

    /**
     * Abandonne la partie.
     */
    public function abandon(Partie $partie)
    {
        $partie->update(['statut' => 'terminee']); // Ou un statut abandon
        return redirect()->route('dashboard')->with('success', 'Partie abandonnée.');
    }

    /**
     * Met à jour le rôle de l'utilisateur dans l'équipe.
     */
    public function updateRole(Request $request, Partie $partie)
    {
        $request->validate([
            'role' => 'required|in:challenger,participant'
        ]);

        $partie->team->users()->updateExistingPivot(auth()->id(), [
            'role' => $request->role
        ]);

        return back()->with('success', 'Rôle mis à jour.');
    }

    /**
     * Affiche les détails d'une partie spécifique.
     */
    public function show(Partie $partie)
    {
        // On vérifie que la partie appartient bien à l'utilisateur
        if ($partie->createur_id !== auth()->id()) {
            abort(403);
        }

        $partie->load(['environnement', 'progression']);

        // Si la partie est terminée, on va sur le résumé, sinon on continue l'énigme
        if ($partie->statut === 'terminee') {
            return redirect()->route('progression.summary', $partie->id);
        }

        return redirect()->route('progression.enigme', $partie->id);
    }
}
