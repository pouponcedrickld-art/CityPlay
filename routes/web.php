<?php

use App\Http\Controllers\TeamJoinRequestController;
use App\Http\Controllers\Admin\EnigmeController;
use App\Http\Controllers\Admin\EnvironnementController;
use App\Http\Controllers\Admin\LieuController as AdminLieuController;
use App\Http\Controllers\Admin\StatController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PartieController as AdminPartieController;
use App\Http\Controllers\Admin\TeamController as AdminTeamController;
use App\Http\Controllers\Admin\AppInvitationController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClassementController;
use App\Http\Controllers\PartieController;
use App\Http\Controllers\ProgressionController;
use App\Http\Controllers\GPSValidationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/**
 * Définition des routes Web du projet CityPlay.
 *
 * Le projet utilise Inertia.js pour le rendu des vues Vue.js.
 * Les routes sont organisées par niveaux d'accès :
 * 1. Public (Accueil, Invitations)
 * 2. Authentifié (Profil, OTP)
 * 3. Vérifié (Jeu, Dashboard, Chat)
 * 4. Administrateur (Gestion du contenu)
 */

// --- ROUTES PUBLIQUES ---

// Invitation équipe : permet de rejoindre via un lien sans être forcément connecté au départ
Route::get('/rejoindre/{code}', [PartieController::class, 'rejoindreParLien'])->name('parties.rejoindre');

// Page d'accueil (Welcome)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// --- ROUTES AUTHENTIFIÉES (Utilisateur connecté) ---
Route::middleware('auth')->group(function () {

    // Système OTP : Vérification obligatoire après login/register
    Route::get('/otp/verify', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');

    // Profil Utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- ROUTES VÉRIFIÉES (OTP Validé ou Admin) ---
    Route::middleware('otp.verified')->group(function () {

        // Dashboard Joueur : Accès aux parties et environnements
        Route::get('/dashboard', [PartieController::class, 'index'])->name('dashboard');

        // Classement Mondial
        Route::get('/classement', [ClassementController::class, 'index'])->name('player.classement');

        // Chat d'équipe (WebSockets via Reverb)
        Route::get('/teams/{team}/messages', [ChatController::class, 'getMessages'])->name('chat.messages');
        Route::post('/teams/{team}/messages', [ChatController::class, 'sendMessage'])->name('chat.send');

        // Système d'adhésion aux équipes
        Route::get('/teams/discover', [TeamJoinRequestController::class, 'discover'])->name('teams.discover');
        Route::post('/teams/{team}/join', [TeamJoinRequestController::class, 'store'])->name('teams.join');
        Route::get('/teams/requests', [TeamJoinRequestController::class, 'index'])->name('teams.requests.index');
        Route::post('/teams/requests/{request}/accept', [TeamJoinRequestController::class, 'accept'])->name('teams.requests.accept');
        Route::post('/teams/requests/{request}/reject', [TeamJoinRequestController::class, 'reject'])->name('teams.requests.reject');

        // Gestion des Parties
        Route::post('/parties/rejoindre', [PartieController::class, 'rejoindre'])->name('parties.rejoindre.form');
        Route::get('/parties', [PartieController::class, 'index'])->name('parties.web.index');
        Route::get('/parties/create', [PartieController::class, 'create'])->name('parties.web.create');
        Route::post('/parties', [PartieController::class, 'store'])->name('parties.web.store');
        Route::get('/parties/{partie}', [PartieController::class, 'show'])->name('parties.web.show');
        Route::get('/parties/{partie}/team-setup', [PartieController::class, 'teamSetup'])->name('parties.team-setup');
        Route::post('/parties/{partie}/update-role', [PartieController::class, 'updateRole'])->name('parties.update-role');
        Route::post('/parties/{partie}/pause', [PartieController::class, 'pause'])->name('parties.web.pause');
        Route::post('/parties/{partie}/abandon', [PartieController::class, 'abandon'])->name('parties.web.abandon');

        // Progression et Gameplay
        Route::get('/parties/{partie}/enigme', [ProgressionController::class, 'getCurrentEnigme'])->name('progression.enigme');
        Route::post('/parties/{partie}/reponse', [ProgressionController::class, 'submitAnswer'])->name('progression.submit');
        Route::post('/parties/{partie}/validate-gps', [GPSValidationController::class, 'validate'])->name('progression.validate-gps');

        Route::get('/parties/{partie}/succes', [ProgressionController::class, 'showSuccess'])->name('progression.success');
        Route::get('/parties/{partie}/echec', [ProgressionController::class, 'showFailure'])->name('progression.failure');
        Route::post('/parties/{partie}/suivant', [ProgressionController::class, 'nextEnigme'])->name('progression.next');
        Route::post('/parties/{partie}/solution', [ProgressionController::class, 'showSolution'])->name('progression.solution');
        Route::get('/parties/{partie}/resume', [ProgressionController::class, 'showSummary'])->name('progression.summary');
        Route::get('/parties/{partie}/carte', [ProgressionController::class, 'showCarte'])->name('progression.carte');
        Route::post('/parties/{partie}/progression', [ProgressionController::class, 'store'])->name('progression.store');

        // Validation GPS
        Route::post('/lieux/{lieu}/valider', [GPSValidationController::class, 'validatePosition'])->name('gps.valider');
    });

    // --- ROUTES ADMINISTRATION ---
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
        Route::get('/lieux/search', [AdminLieuController::class, 'searchLocation'])->name('admin.lieux.search');
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


require __DIR__ . '/auth.php';
