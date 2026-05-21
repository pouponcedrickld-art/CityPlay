<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Partie;
use App\Models\Environnement;
use App\Services\PartieService;
use App\Services\GeoService;
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
    public function index(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $parties = Partie::where('createur_id', auth()->id())
            ->with(['environnement', 'progression.enigmeCourante.lieu', 'team.users'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Partie $partie) {
                if ($partie->mode === 'team' && $partie->code_liaison) {
                    $partie->setAttribute('lien_invitation', $partie->getLienInvitation());
                    $partie->setAttribute('nb_membres', $partie->team?->users->count() ?? 0);
                }

                return $partie;
            });

        $userLat = $request->filled('latitude') ? (float) $request->latitude : null;
        $userLng = $request->filled('longitude') ? (float) $request->longitude : null;

        $environnements = Environnement::where('actif', true)
            ->withCount('lieux')
            ->with(['lieux' => fn ($q) => $q->where('actif', true)->select('id', 'environnement_id', 'latitude', 'longitude')])
            ->get()
            ->map(function (Environnement $env) use ($userLat, $userLng) {
                $centroid = GeoService::centroidFromLieux($env->lieux);
                $env->unsetRelation('lieux');

                $env->latitude = $centroid['latitude'] ?? null;
                $env->longitude = $centroid['longitude'] ?? null;

                if ($userLat !== null && $userLng !== null && $env->latitude && $env->longitude) {
                    $env->distance_km = round(
                        GeoService::distanceKm($userLat, $userLng, $env->latitude, $env->longitude),
                        1
                    );
                }

                return $env;
            });

        $geolocalise = $userLat !== null && $userLng !== null;

        if ($geolocalise) {
            $environnements = $environnements
                ->sortBy(fn ($env) => $env->distance_km ?? PHP_FLOAT_MAX)
                ->values();
        }

        return Inertia::render('Player/Dashboard', [
            'parties' => $parties,
            'environnements' => $environnements->take(4)->values(),
            'geolocalise' => $geolocalise,
            'topPlayers' => User::where('is_admin', false)
                ->orderByDesc('total_score')
                ->limit(3)
                ->get(['id', 'name', 'total_score']),
        ]);
    }

    /**
     * Rejoindre une partie via le code d'invitation (compte existant)
     */
    public function rejoindre(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $input = trim($request->code);
        $code = str_contains($input, '/rejoindre/')
            ? $this->extraireCodeDepuisLien($input)
            : strtoupper(preg_replace('/\s+/', '', $input));

        if (!$code || !preg_match('/^[A-Z0-9\-]{6,12}$/i', $code)) {
            return back()->withErrors([
                'code' => 'Code d\'invitation invalide. Vérifiez le code reçu de l\'organisateur.',
            ]);
        }

        return redirect()->route('parties.rejoindre', $code);
    }

    /**
     * Point d'entrée public du lien d'invitation équipe
     */
    public function rejoindreParLien(string $code)
    {
        $code = strtoupper(trim($code));
        $partie = Partie::with('environnement')->where('code_liaison', $code)->first();

        if (!$partie) {
            return redirect()->route('register')
                ->with('error', 'Lien d\'invitation invalide ou expiré.');
        }

        if ($partie->mode !== 'team') {
            return redirect()->route('register')
                ->with('error', 'Ce lien ne correspond pas à une partie en équipe.');
        }

        if ($partie->estExpiree()) {
            return redirect()->route('register')
                ->with('error', 'Ce lien d\'invitation a expiré.');
        }

        session(['partie_invitation_code' => $code]);

        if (!auth()->check()) {
            return redirect()->route('register')
                ->with('info', 'Créez un compte ou connectez-vous pour rejoindre l\'équipe « ' . ($partie->environnement?->nom ?? 'CityPlay') . ' ».');
        }

        $user = auth()->user();

        if (!$user->otp_verified_at) {
            return redirect()->route('otp.show')
                ->with('info', 'Vérifiez votre compte pour rejoindre la partie.');
        }

        return $this->finaliserRejoindrePartie($partie);
    }

    /**
     * Intègre le joueur connecté à la partie puis redirige vers le bon écran
     */
    private function finaliserRejoindrePartie(Partie $partie)
    {
        if ($partie->mode === 'team' && $partie->team) {
            $dejaMembre = $partie->team->users()->where('user_id', auth()->id())->exists();

            if (!$dejaMembre) {
                $nbJoueursMax = $partie->parametres['nb_joueurs'] ?? 10;
                if ($partie->team->users()->count() >= $nbJoueursMax) {
                    session()->forget('partie_invitation_code');
                    return redirect()->route('dashboard')
                        ->with('error', 'L\'équipe est déjà complète.');
                }
                $partie->team->users()->attach(auth()->id(), ['role' => 'participant']);
            }

            session()->forget('partie_invitation_code');

            if ($partie->statut === 'en_attente') {
                return redirect()->route('parties.team-setup', $partie)
                    ->with('success', 'Bienvenue dans l\'équipe ! Choisissez votre rôle.');
            }
        }

        session()->forget('partie_invitation_code');

        return redirect()->route('progression.enigme', $partie);
    }

    /**
     * Redirection après login / OTP si une invitation partie est en attente
     */
    public static function redirectApresAuthentification()
    {
        $user = auth()->user();

        if ($code = session('partie_invitation_code')) {
            if (!$user->otp_verified_at) {
                return redirect()->route('otp.show')
                    ->with('info', 'Vérifiez votre compte pour rejoindre la partie.');
            }

            return redirect()->route('parties.rejoindre', $code);
        }

        if ($user->is_admin || $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Infos d'invitation partie stockées en session (pour register/login)
     */
    public static function invitationPartieEnSession(): ?array
    {
        $code = session('partie_invitation_code');
        if (!$code) {
            return null;
        }

        $partie = Partie::with('environnement')->where('code_liaison', $code)->first();
        if (!$partie || $partie->estExpiree() || $partie->mode !== 'team') {
            session()->forget('partie_invitation_code');
            return null;
        }

        return [
            'code' => $code,
            'environnement' => $partie->environnement?->nom,
        ];
    }

    /**
     * Extrait le code de liaison depuis une URL ou une saisie brute
     */
    private function extraireCodeDepuisLien(string $input): ?string
    {
        $input = trim($input);

        if (preg_match('#/rejoindre/([^/?\s]+)#i', $input, $matches)) {
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

        $partie = $partie->fresh()->load('environnement', 'team');
        $partie->setAttribute('lien_invitation', $partie->getLienInvitation());
        $partie->setAttribute('code_invitation', $partie->code_liaison);
        $partie->setAttribute('nb_membres', $partie->team?->users()->count() ?? 1);

        return Inertia::render('Player/JoinPartie', [
            'partie' => $partie,
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
