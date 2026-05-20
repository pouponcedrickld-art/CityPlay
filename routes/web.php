<?php

use App\Http\Controllers\Admin\EnigmeController;
use App\Http\Controllers\Admin\EnvironnementController;
use App\Http\Controllers\Admin\LieuController as AdminLieuController;
use App\Http\Controllers\Admin\StatController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PartieController as AdminPartieController;
use App\Http\Controllers\Admin\TeamController as AdminTeamController;
use App\Http\Controllers\Admin\AppInvitationController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PartieController;
use App\Http\Controllers\ProgressionController;
use App\Http\Controllers\GPSValidationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Invitation équipe (accessible sans compte)
Route::get('/rejoindre/{code}', [PartieController::class, 'rejoindreParLien'])->name('parties.rejoindre');

// Invitation admin (clé d'accès) : URL courte
Route::get('/invite/{token}', function (string $token) {
    return redirect()->route('register', ['invite' => $token]);
})->name('invite.redirect');

// Page d'accueil
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Routes protégées (utilisateur connecté)
Route::middleware('auth')->group(function () {

    // OTP (Accessible même si non vérifié, évidemment)
    Route::get('/otp/verify', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Routes nécessitant la vérification OTP
    Route::middleware('otp.verified')->group(function () {
        // Test Game
        Route::get('/test-game', function() {
            $user = auth()->user();
            $env = \App\Models\Environnement::where('actif', true)->first();
            if (!$env) return "Aucun environnement actif trouvé.";

            $data = [
                'environnement_id' => $env->id,
                'mode' => 'solo',
                'duree' => 60,
                'locomotion' => 'pied',
                'difficulte' => 2,
                'nb_joueurs' => 1
            ];

            try {
                $service = app(\App\Services\PartieService::class);
                $partie = $service->creerPartie($data, $user->id);
                return redirect()->route('progression.enigme', $partie);
            } catch (\Exception $e) {
                return "Erreur : " . $e->getMessage();
            }
        });

        // Dashboard joueur
        Route::get('/dashboard', [PartieController::class, 'index'])->name('dashboard');

        Route::post('/parties/rejoindre', [PartieController::class, 'rejoindre'])->name('parties.rejoindre.form');

        // Parties
        Route::get('/parties', [PartieController::class, 'index'])->name('parties.web.index');
        Route::get('/parties/create', [PartieController::class, 'create'])->name('parties.web.create');
        Route::post('/parties', [PartieController::class, 'store'])->name('parties.web.store');
        Route::get('/parties/{partie}', [PartieController::class, 'show'])->name('parties.web.show');
        Route::get('/parties/{partie}/team-setup', [PartieController::class, 'teamSetup'])->name('parties.team-setup');
        Route::post('/parties/{partie}/update-role', [PartieController::class, 'updateRole'])->name('parties.update-role');
        Route::post('/parties/{partie}/pause', [PartieController::class, 'pause'])->name('parties.web.pause');
        Route::post('/parties/{partie}/abandon', [PartieController::class, 'abandon'])->name('parties.web.abandon');

        // Progression
        Route::get('/parties/{partie}/enigme', [ProgressionController::class, 'getCurrentEnigme'])->name('progression.enigme');
        Route::post('/parties/{partie}/reponse', [ProgressionController::class, 'submitAnswer'])->name('progression.submit');
        Route::get('/parties/{partie}/succes', [ProgressionController::class, 'showSuccess'])->name('progression.success');
        Route::get('/parties/{partie}/echec', [ProgressionController::class, 'showFailure'])->name('progression.failure');
        Route::post('/parties/{partie}/suivant', [ProgressionController::class, 'nextEnigme'])->name('progression.next');
        Route::post('/parties/{partie}/solution', [ProgressionController::class, 'showSolution'])->name('progression.solution');
        Route::get('/parties/{partie}/resume', [ProgressionController::class, 'showSummary'])->name('progression.summary');
        Route::post('/parties/{partie}/progression', [ProgressionController::class, 'store'])->name('progression.store');

        // Validation GPS
        Route::post('/lieux/{lieu}/valider', [GPSValidationController::class, 'validatePosition'])->name('gps.valider');

        // Admin routes
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', [StatController::class, 'dashboard'])->name('admin.dashboard');

            // Villes (gérées par EnvironnementController)
            Route::get('/villes', [EnvironnementController::class, 'indexVilles'])->name('admin.villes.index');
            Route::post('/villes', [EnvironnementController::class, 'storeVille'])->name('admin.villes.store');
            Route::put('/villes/{ville}', [EnvironnementController::class, 'updateVille'])->name('admin.villes.update');
            Route::delete('/villes/{ville}', [EnvironnementController::class, 'destroyVille'])->name('admin.villes.destroy');

            // Environnements
            Route::resource('environnements', EnvironnementController::class)->names('admin.environnements');

            // Lieux (dépendent d'un environnement)
            Route::get('/environnements/{environnement}/lieux', [AdminLieuController::class, 'index'])->name('admin.lieux.index');
            Route::get('/environnements/{environnement}/lieux/create', [AdminLieuController::class, 'create'])->name('admin.lieux.create');
            Route::post('/environnements/{environnement}/lieux', [AdminLieuController::class, 'store'])->name('admin.lieux.store');
            Route::get('/environnements/{environnement}/lieux/{lieu}/edit', [AdminLieuController::class, 'edit'])->name('admin.lieux.edit');
            Route::put('/environnements/{environnement}/lieux/{lieu}', [AdminLieuController::class, 'update'])->name('admin.lieux.update');
            Route::delete('/environnements/{environnement}/lieux/{lieu}', [AdminLieuController::class, 'destroy'])->name('admin.lieux.destroy');

            // Énigmes (dépendent d'un lieu)
            Route::get('/lieux/{lieu}/enigmes', [EnigmeController::class, 'index'])->name('admin.enigmes.index');
            Route::get('/lieux/{lieu}/enigmes/{type}/edit', [EnigmeController::class, 'edit'])->name('admin.enigmes.edit');
            Route::post('/lieux/{lieu}/enigmes/{type}', [EnigmeController::class, 'upsert'])->name('admin.enigmes.upsert');
            Route::delete('/lieux/{lieu}/enigmes/{type}', [EnigmeController::class, 'destroy'])->name('admin.enigmes.destroy');

            // Autres ressources admin
            Route::resource('users', AdminUserController::class)->names('admin.users');
            Route::resource('parties', AdminPartieController::class)->names('admin.parties');
            Route::resource('teams', AdminTeamController::class)->names('admin.teams');
            Route::resource('invitations', AppInvitationController::class)->names('admin.invitations');
        });
    });
});

require __DIR__ . '/auth.php';
