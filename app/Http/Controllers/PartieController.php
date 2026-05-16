<?php

namespace App\Http\Controllers;

use App\Models\Partie;
use App\Models\Environnement;
use App\Models\Progression;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PartieController extends Controller
{
    /**
     * Affiche la liste des parties de l'utilisateur.
     * C'est le tableau de bord principal du joueur.
     */
    public function index()
    {
        $parties = Partie::where('user_id', auth()->id())
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
        ]);

        $environnement = Environnement::with('lieux')->find($request->environnement_id);

        // Création de la partie
        $partie = Partie::create([
            'user_id' => auth()->id(),
            'environnement_id' => $request->environnement_id,
            'code_partie' => strtoupper(Str::random(6)), // Code unique pour rejoindre/partager
            'statut' => 'en_cours',
            'score_total' => 0,
        ]);

        // Initialisation de la progression
        // On récupère tous les IDs des lieux de l'environnement pour définir le parcours
        $lieuxIds = $environnement->lieux->pluck('id')->toArray();

        Progression::create([
            'partie_id' => $partie->id,
            'user_id' => auth()->id(),
            'lieux_restants' => $lieuxIds,
            'lieux_decouverts' => [],
            'nb_enigmes_resolues' => 0,
            'temps_restant_minutes' => $environnement->duree_estimee ?? 60,
        ]);

        return redirect()->route('progression.enigme', $partie->id)
            ->with('success', 'La partie a commencé ! Bonne chance.');
    }

    /**
     * Affiche les détails d'une partie spécifique.
     */
    public function show(Partie $partie)
    {
        // On vérifie que la partie appartient bien à l'utilisateur
        if ($partie->user_id !== auth()->id()) {
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
