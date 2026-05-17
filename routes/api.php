<?php

use App\Http\Controllers\Api\GameplayApiController;
use App\Http\Controllers\Api\PartieApiController;
use App\Http\Controllers\Api\ProgressionApiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

/*
|--------------------------------------------------------------------------
| API Routes - CityPlay Jour 3 (Sécurité)
|--------------------------------------------------------------------------
| Rate limit : 5 login/minute
| Middleware : auth:sanctum
*/

// === RATE LIMIT LOGIN ===
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

Route::middleware(['auth:sanctum'])->group(function () {

    // PARTIES
    Route::apiResource('parties', PartieApiController::class);
    Route::post('parties/{partie}/invitation', [PartieApiController::class, 'genererInvitation']);
    Route::post('parties/rejoindre', [PartieApiController::class, 'rejoindreParCode']);

    // PROGRESSION
    Route::get('parties/{partie}/progression', [ProgressionApiController::class, 'show']);

    // GAMEPLAY
    Route::post('parties/{partie}/demarrer', [GameplayApiController::class, 'demarrer']);
    Route::post('parties/{partie}/valider-gps', [GameplayApiController::class, 'validerGps']);
    Route::post('parties/{partie}/passer-enigme', [GameplayApiController::class, 'passerEnigme']);
    Route::post('parties/{partie}/indice', [GameplayApiController::class, 'demanderIndice']);
    Route::post('parties/{partie}/solution', [GameplayApiController::class, 'revelerSolution']);
    Route::post('parties/{partie}/pause', [GameplayApiController::class, 'mettreEnPause']);
    Route::post('parties/{partie}/reprendre', [GameplayApiController::class, 'reprendre']);
    Route::post('parties/{partie}/terminer', [GameplayApiController::class, 'terminer']);

});
