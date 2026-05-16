<?php

use App\Http\Controllers\Admin\EnigmeController;
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
    })->name('player.dashboard');

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
    Route::post('/parties/{partie}/reponse', [ProgressionController::class, 'submitAnswer'])->name('progression.submit');
    Route::get('/parties/{partie}/succes', [ProgressionController::class, 'showSuccess'])->name('progression.success');
    Route::get('/parties/{partie}/echec', [ProgressionController::class, 'showFailure'])->name('progression.failure');
    Route::post('/parties/{partie}/suivant', [ProgressionController::class, 'nextEnigme'])->name('progression.next');
    Route::get('/parties/{partie}/resume', [ProgressionController::class, 'showSummary'])->name('progression.summary');
    Route::post('/parties/{partie}/progression', [ProgressionController::class, 'store'])->name('progression.store');

    // Validation GPS
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

require __DIR__ . '/auth.php';
