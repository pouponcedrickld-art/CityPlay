<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',        // ← AJOUTÉ
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->redirectUsersTo(function () {
            $user = auth()->user();

            if ($code = session('partie_invitation_code')) {
                if (!$user->otp_verified_at) {
                    return route('otp.show');
                }
                return route('parties.rejoindre', $code);
            }

            if ($user && ($user->is_admin || $user->hasRole('admin'))) {
                return route('admin.dashboard');
            }

            return route('dashboard');
        });

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'otp.verified' => \App\Http\Middleware\EnsureOtpVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
