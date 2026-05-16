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
        ]);

        $data = [
            'environnement_id' => $request->environnement_id,
            'mode' => 'solo',
            'duree_prevue' => 60,
            'locomotion' => 'pied',
            'difficulte' => 2,
        ];

        $partie = $this->partieService->creerPartie($data, auth()->id());

        return redirect()->route('progression.enigme', $partie->id)
            ->with('success', 'La partie a commencé ! Bonne chance.');
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
