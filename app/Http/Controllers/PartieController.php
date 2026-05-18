<?php

namespace App\Http\Controllers;

use App\Models\Partie;
use App\Models\Environnement;
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
     * Liste les parties du joueur (Dashboard Joueur)
     */
    public function index()
    {
        $parties = Partie::where('createur_id', auth()->id())
            ->with(['environnement', 'progression.enigmeCourante.lieu'])
            ->orderBy('created_at', 'desc')
            ->get();

        $environnements = Environnement::where('actif', true)
            ->withCount('lieux')
            ->get();

        return Inertia::render('Player/Dashboard', [
            'parties' => $parties,
            'environnements' => $environnements
        ]);
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        $environnements = Environnement::where('actif', true)
            ->withCount('lieux')
            ->get();

        return Inertia::render('Player/CreatePartie', [
            'environnements' => $environnements,
        ]);
    }

    /**
     * Affiche une partie spécifique
     */
    public function show(Partie $partie)
    {
        $this->authorize('view', $partie);

        return Inertia::render('Player/Dashboard', [
            'partie' => $partie->load('environnement', 'progression'),
        ]);
    }

    /**
     * Crée une nouvelle partie
     */
    public function store(Request $request)
    {
        \Log::info('Tentative de création de partie', $request->all());

        try {
            $validated = $request->validate([
                'environnement_id' => 'required|exists:environnements,id',
                'mode' => 'required|in:solo,team',
                'duree' => 'required|integer|min:15|max:300',
                'locomotion' => 'required|in:pied,velo,voiture,moto',
                'difficulte' => 'required|integer|between:1,3',
                'nb_joueurs' => 'required|integer|between:1,10',
            ]);

            \Log::info('Validation réussie', $validated);

            $data = [
                'environnement_id' => $validated['environnement_id'],
                'mode' => $validated['mode'],
                'duree' => $validated['duree'],
                'locomotion' => $validated['locomotion'],
                'difficulte' => $validated['difficulte'],
                'nb_joueurs' => $validated['nb_joueurs'],
            ];

            $partie = $this->partieService->creerPartie($data, auth()->id());
            
            \Log::info('Partie créée avec succès', ['id' => $partie->id]);

            if ($partie->mode === 'solo') {
                return redirect()->route('progression.enigme', $partie)
                    ->with('success', 'Votre mission commence !');
            }

            return redirect()->route('parties.web.show', $partie)
                ->with('success', 'Partie créée avec succès ! Invitez vos coéquipiers.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation lors de la création de partie', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Erreur inattendue lors de la création de partie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Impossible de créer la partie : ' . $e->getMessage());
        }
    }

    /**
     * Génère un code d'invitation
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

    /**
     * Met la partie en pause ou la reprend
     */
    public function pause(Partie $partie)
    {
        $progression = $partie->progression;
        if (!$progression) return back();

        $nouveauStatut = $progression->statut === 'pause' ? 'en_cours' : 'pause';
        $progression->update(['statut' => $nouveauStatut]);

        $message = $nouveauStatut === 'pause' ? 'Mission suspendue.' : 'Mission reprise !';
        return back()->with('success', $message);
    }

    /**
     * Abandonne la partie
     */
    public function abandon(Partie $partie)
    {
        $progression = $partie->progression;
        if ($progression) {
            $progression->update(['statut' => 'terminee']);
        }
        
        $partie->update(['statut' => 'terminee', 'ended_at' => now()]);

        return redirect()->route('dashboard')->with('success', 'Mission abandonnée.');
    }
}
