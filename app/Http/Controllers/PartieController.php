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
     * Rejoindre une partie via un lien collé dans le formulaire
     */
    public function rejoindre(Request $request)
    {
        $request->validate([
            'lien' => 'required|string|max:500',
        ]);

        $code = $this->extraireCodeDepuisLien($request->lien);

        if (!$code) {
            return back()->with('error', 'Lien d\'invitation invalide. Vérifiez l\'URL reçue.');
        }

        return redirect()->route('parties.rejoindre', $code);
    }

    /**
     * Rejoindre une partie via un lien direct
     */
    public function rejoindreParLien(string $code)
    {
        $code = strtoupper(trim($code));
        $partie = Partie::where('code_liaison', $code)->first();

        if (!$partie) {
            return redirect()->route('parties.web.create')
                ->with('error', 'Lien d\'invitation invalide ou expiré.');
        }

        if ($partie->mode === 'solo' && $partie->createur_id !== auth()->id()) {
            return redirect()->route('parties.web.create')
                ->with('error', 'Cette partie est privée.');
        }

        if ($partie->estExpiree()) {
            return redirect()->route('parties.web.create')
                ->with('error', 'Ce lien d\'invitation a expiré.');
        }

        if ($partie->mode === 'team' && $partie->team) {
            $dejaMembre = $partie->team->users()->where('user_id', auth()->id())->exists();

            if (!$dejaMembre && $partie->createur_id !== auth()->id()) {
                $nbJoueursMax = $partie->parametres['nb_joueurs'] ?? 10;
                if ($partie->team->users()->count() >= $nbJoueursMax) {
                    return redirect()->route('parties.web.create')
                        ->with('error', 'L\'équipe est déjà complète.');
                }
                $partie->team->users()->attach(auth()->id(), ['role' => 'participant']);
            }

            if ($partie->statut === 'en_attente') {
                return redirect()->route('parties.team-setup', $partie)
                    ->with('success', 'Bienvenue dans l\'équipe ! Choisissez votre rôle.');
            }
        }

        return redirect()->route('progression.enigme', $partie);
    }

    /**
     * Extrait le code de liaison depuis une URL ou une saisie brute
     */
    private function extraireCodeDepuisLien(string $input): ?string
    {
        $input = trim($input);

        if (preg_match('#/rejoindre/([^/?\s#]+)#i', $input, $matches)) {
            return strtoupper(urldecode($matches[1]));
        }

        if (preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/i', $input)) {
            return strtoupper($input);
        }

        if (preg_match('/^[A-Z0-9]{6,12}$/i', $input)) {
            return strtoupper($input);
        }

        return null;
    }

    /**
     * Affiche le formulaire de création
     */
    public function create(Request $request)
    {
        $environnements = Environnement::where('actif', true)
            ->withCount('lieux')
            ->get();

        return Inertia::render('Player/CreatePartie', [
            'environnements' => $environnements,
            'tab' => $request->query('tab', 'create'),
        ]);
    }

    /**
     * Affiche une partie spécifique (redirige vers le bon écran)
     */
    public function show(Partie $partie)
    {
        $this->authorize('view', $partie);

        if ($partie->mode === 'team' && $partie->statut === 'en_attente') {
            return redirect()->route('parties.team-setup', $partie);
        }

        return redirect()->route('progression.enigme', $partie);
    }

    /**
     * Configuration équipe : choix du rôle et partage du code
     */
    public function teamSetup(Partie $partie)
    {
        $this->authorize('view', $partie);

        if ($partie->mode !== 'team') {
            return redirect()->route('progression.enigme', $partie);
        }

        if (!$partie->lien_partage && $partie->code_liaison) {
            $partie->update(['lien_partage' => $partie->genererLienPartage()]);
        }

        return Inertia::render('Player/JoinPartie', [
            'partie' => $partie->fresh()->load('environnement', 'team'),
        ]);
    }

    /**
     * Met à jour le rôle du joueur dans l'équipe
     */
    public function updateRole(Request $request, Partie $partie)
    {
        $this->authorize('view', $partie);

        $validated = $request->validate([
            'role' => 'required|in:challenger,participant',
        ]);

        if ($partie->team) {
            $partie->team->users()->updateExistingPivot(auth()->id(), [
                'role' => $validated['role'],
            ]);
        }

        return redirect()->route('progression.enigme', $partie);
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

            return redirect()->route('parties.team-setup', $partie)
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
            'lien_partage' => route('parties.rejoindre', $code),
            'expire_at' => now()->addHours(
                $partie->environnement->duree_vie_lien_heures ?? 24
            ),
            'verrouillee' => true,
        ]);

        return back()->with('success', 'Lien d\'invitation régénéré.');
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
