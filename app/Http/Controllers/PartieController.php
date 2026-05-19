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
     * Rejoindre une partie via un lien direct ou un code
     */
    public function rejoindreParLien(string $code)
    {
        $partie = Partie::where('code_liaison', $code)->first();

        if (!$partie) {
            return redirect()->route('dashboard')->with('error', 'Lien ou code d\'invitation invalide.');
        }

        // Si la partie appartient à quelqu'un d'autre et est en solo, erreur
        if ($partie->mode === 'solo' && $partie->createur_id !== auth()->id()) {
            return redirect()->route('dashboard')->with('error', 'Cette partie est privée.');
        }

        // Si la partie a expiré
        if ($partie->expire_at && $partie->expire_at->isPast()) {
            return redirect()->route('dashboard')->with('error', 'Ce lien d\'invitation a expiré.');
        }

        // Si c'est une partie en équipe, on l'ajoute à l'équipe si pas déjà fait
        if ($partie->mode === 'team' && $partie->createur_id !== auth()->id()) {
            if ($partie->team) {
                $dejaMembre = $partie->team->users()->where('user_id', auth()->id())->exists();
                if (!$dejaMembre) {
                    $nbJoueursMax = $partie->parametres['nb_joueurs'] ?? 10;
                    if ($partie->team->users()->count() >= $nbJoueursMax) {
                        return redirect()->route('dashboard')->with('error', 'L\'équipe est déjà complète.');
                    }
                    $partie->team->users()->attach(auth()->id(), ['role' => 'challenger']);
                }
            }
        }

        // Redirection vers le jeu
        return redirect()->route('progression.enigme', $partie);
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
