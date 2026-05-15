<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/', fn() => inertia('Admin/Dashboard'))->name('dashboard');

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
        Route::get('/',                    [LieuController::class, 'index'])->name('index');
        Route::get('/create',              [LieuController::class, 'create'])->name('create');
        Route::post('/',                   [LieuController::class, 'store'])->name('store');
        Route::get('/{lieu}/edit',         [LieuController::class, 'edit'])->name('edit');
        Route::post('/{lieu}',             [LieuController::class, 'update'])->name('update'); // POST car multipart
        Route::delete('/{lieu}',           [LieuController::class, 'destroy'])->name('destroy');
        Route::post('/reorder',            [LieuController::class, 'reorder'])->name('reorder');
    });

    // ── Énigmes (imbriquées dans un lieu) ───────────
    Route::prefix('lieux/{lieu}/enigmes')->name('enigmes.')->group(function () {
        Route::get('/',                    [EnigmeController::class, 'index'])->name('index');
        Route::get('/{type}/edit',         [EnigmeController::class, 'edit'])->name('edit');
        Route::post('/{type}',             [EnigmeController::class, 'upsert'])->name('upsert'); // POST car multipart
        Route::delete('/{type}',           [EnigmeController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
