<?php

use App\Http\Controllers\Admin\EnigmeController;
use App\Http\Controllers\Admin\EnvironnementController;
use App\Http\Controllers\Admin\LieuController as AdminLieuController;
use App\Http\Controllers\Admin\StatController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PartieController as AdminPartieController;
use App\Http\Controllers\Admin\TeamController as AdminTeamController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\PartieController;
use App\Http\Controllers\ProgressionController;
use App\Http\Controllers\GPSValidationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

    // Dashboard joueur
    Route::get('/dashboard', function () {
        return Inertia::render('Player/Dashboard');
    })->name('dashboard');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // OTP
    Route::get('/otp/verify', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');

    // Parties
    Route::post('/parties', [PartieController::class, 'store'])->name('parties.store');
    Route::get('/parties/{partie}', [PartieController::class, 'show'])->name('parties.show');
    Route::get('/parties', [PartieController::class, 'index'])->name('parties.index');

    // Progression
    Route::get('/parties/{partie}/enigme', [ProgressionController::class, 'getCurrentEnigme'])->name('progression.enigme');
    Route::post('/parties/{partie}/progression', [ProgressionController::class, 'store'])->name('progression.store');

    // GPS validation
    Route::post('/lieux/{lieu}/valider', [GPSValidationController::class, 'validatePosition'])->name('gps.valider');

    // Admin énigmes
    Route::prefix('/CreateEnigmes')->group(function () {
        Route::get('/', [EnigmeController::class, 'index'])->name('enigmes.index');
        Route::get('/create', [EnigmeController::class, 'create'])->name('enigmes.create');
        Route::post('/', [EnigmeController::class, 'store'])->name('enigmes.store');
        Route::get('/{enigme}/edit', [EnigmeController::class, 'edit'])->name('enigmes.edit');
        Route::put('/{enigme}', [EnigmeController::class, 'update'])->name('enigmes.update');
        Route::delete('/{enigme}', [EnigmeController::class, 'destroy'])->name('enigmes.destroy');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/', [StatController::class, 'dashboard'])->name('dashboard');

    // ── Villes ──────────────────────────────────────
    Route::prefix('villes')->name('villes.')->group(function () {
        Route::get('/',           [EnvironnementController::class, 'indexVilles'])->name('index');
        Route::post('/',          [EnvironnementController::class, 'storeVille'])->name('store');
        Route::put('/{ville}',    [EnvironnementController::class, 'updateVille'])->name('update');
        Route::delete('/{ville}', [EnvironnementController::class, 'destroyVille'])->name('destroy');
    });

    // ── Environnements ───────────────────────────────
    Route::prefix('environnements')->name('environnements.')->group(function () {
        Route::get('/',                       [EnvironnementController::class, 'index'])->name('index');
        Route::get('/create',                 [EnvironnementController::class, 'create'])->name('create');
        Route::post('/',                      [EnvironnementController::class, 'store'])->name('store');
        Route::get('/{environnement}/edit',   [EnvironnementController::class, 'edit'])->name('edit');
        Route::put('/{environnement}',        [EnvironnementController::class, 'update'])->name('update');
        Route::delete('/{environnement}',     [EnvironnementController::class, 'destroy'])->name('destroy');
    });

    // ── Lieux (imbriqués dans un environnement) ──────
    Route::prefix('environnements/{environnement}/lieux')->name('lieux.')->group(function () {
        Route::get('/',                    [AdminLieuController::class, 'index'])->name('index');
        Route::get('/create',              [AdminLieuController::class, 'create'])->name('create');
        Route::post('/',                   [AdminLieuController::class, 'store'])->name('store');
        Route::get('/{lieu}/edit',         [AdminLieuController::class, 'edit'])->name('edit');
        Route::post('/{lieu}',             [AdminLieuController::class, 'update'])->name('update'); // POST car multipart
        Route::delete('/{lieu}',           [AdminLieuController::class, 'destroy'])->name('destroy');
        Route::post('/reorder',            [AdminLieuController::class, 'reorder'])->name('reorder');
    });

    // ── Énigmes (imbriquées dans un lieu) ───────────
    Route::prefix('lieux/{lieu}/enigmes')->name('enigmes.')->group(function () {
        Route::get('/',                    [EnigmeController::class, 'index'])->name('index');
        Route::get('/{type}/edit',         [EnigmeController::class, 'edit'])->name('edit');
        Route::post('/{type}',             [EnigmeController::class, 'upsert'])->name('upsert'); // POST car multipart
        Route::delete('/{type}',           [EnigmeController::class, 'destroy'])->name('destroy');
    });

    // ── Utilisateurs ────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',           [AdminUserController::class, 'index'])->name('index');
        Route::delete('/{user}',  [AdminUserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('toggle-admin');
    });

    // ── Parties ─────────────────────────────────────
    Route::prefix('parties')->name('parties.')->group(function () {
        Route::get('/',           [AdminPartieController::class, 'index'])->name('index');
        Route::delete('/{partie}', [AdminPartieController::class, 'destroy'])->name('destroy');
    });

    // ── Équipes ──────────────────────────────────────
    Route::prefix('teams')->name('teams.')->group(function () {
        Route::get('/',           [AdminTeamController::class, 'index'])->name('index');
        Route::delete('/{team}',  [AdminTeamController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
