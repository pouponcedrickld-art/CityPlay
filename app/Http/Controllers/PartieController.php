<?php

namespace App\Http\Controllers;

use App\Models\Partie;
use App\Services\PartieService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PartieController extends Controller
{
    protected $partieService;

    public function __construct(PartieService $partieService)
    {
        $this->partieService = $partieService;
    }

    /**
     * Affiche la liste des parties de l'utilisateur.
     */
    public function index()
    {
        $parties = Partie::where('createur_id', auth()->id())
            ->with('environnement')
            ->get();

        return Inertia::render('Player/Parties', [
            'parties' => $parties,
        ]);
    }

    /**
     * Affiche une partie spécifique (vue Inertia)
     */
    public function show(Partie $partie)
    {
        $this->authorize('view', $partie);

        return Inertia::render('Player/PartieShow', [
            'partie' => $partie->load('environnement', 'progression'),
        ]);
    }

    /**
     * Crée une nouvelle partie.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'environnement_id' => 'required|exists:environnements,id',
            'mode' => 'required|in:solo,team',
            'parametres' => 'required|array',
            'parametres.duree' => 'required|integer|min:15|max:300',
            'parametres.locomotion' => 'required|in:pied,velo,voiture,moto',
            'parametres.difficulte' => 'required|integer|between:1,3',
            'parametres.nb_joueurs' => 'required|integer|between:1,9',
        ]);

        $partie = $this->partieService->creerPartie($validated);

        return redirect()->route('parties.show', $partie)
            ->with('success', 'Partie créée avec succès !');
    }

    /**
     * Génère un code d'invitation pour une partie.
     */
    public function genererInvitation(Partie $partie)
    {
        $this->authorize('update', $partie);

        $code = Str::upper(Str::random(4)) . '-' . Str::random(4);

        $partie->update([
            'code_liaison' => $code,
            'expire_at' => now()->addHours(
                $partie->environnement->duree_vie_lien_heures ?? 24
            ),
            'verrouillee' => true,
        ]);

        return back()->with('success', "Code généré : $code");
    }
}
