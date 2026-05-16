<?php

namespace App\Http\Controllers;

use App\Models\Partie;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * PartieController (Jour 1)
 * 
 * Gère les parties pour le front web (Inertia.js/Vue).
 * Retourne des vues, pas du JSON.
 */
class PartieController extends Controller
{
    /**
     * Liste les parties du joueur (vue Inertia)
     */
    public function index()
    {
        $parties = Partie::where('createur_id', auth()->id())
            ->with('environnement')
            ->get();

        return Inertia::render('Player/Parties/Index', [
            'parties' => $parties,
        ]);
    }

    /**
     * Affiche une partie spécifique (vue Inertia)
     */
    public function show(Partie $partie)
    {
        return Inertia::render('Player/Parties/Show', [
            'partie' => $partie->load('environnement', 'progression'),
        ]);
    }

    /**
     * Crée une nouvelle partie (depuis le formulaire web)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'environnement_id' => 'required|exists:environnements,id',
            'mode' => 'required|in:solo,team',
            'parametres.duree' => 'required|integer|min:15|max:300',
            'parametres.locomotion' => 'required|in:pied,velo,voiture,moto',
            'parametres.difficulte' => 'required|integer|in:1,2,3',
            'parametres.nb_joueurs' => 'required|integer|min:1|max:9',
        ]);

        $partie = Partie::create([
            'createur_id' => auth()->id(),
            'environnement_id' => $validated['environnement_id'],
            'mode' => $validated['mode'],
            'parametres' => $validated['parametres'],
            'statut' => 'en_attente',
        ]);

        return redirect()->route('parties.show', $partie)
            ->with('success', 'Partie créée avec succès.');
    }
}