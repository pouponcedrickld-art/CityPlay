<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de sécurité pour restreindre l'accès à l'interface d'administration.
 */
class AdminMiddleware
{
    /**
     * Vérifie si l'utilisateur est authentifié et possède les droits d'administration.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. L'utilisateur doit être connecté
        if (!$user) {
            return redirect()->route('login');
        }

        // Log de tentative d'accès pour audit
        \Illuminate\Support\Facades\Log::info('Admin access check', [
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'has_role_admin' => $user->hasRole('admin')
        ]);

        // 2. Vérification double : soit via le booléen is_admin, soit via le rôle Spatie 'admin'
        if (!$user->is_admin && !$user->hasRole('admin')) {
            abort(403, 'Accès réservé aux administrateurs. Veuillez contacter le support si vous pensez qu\'il s\'agit d\'une erreur.');
        }

        return $next($request);
    }
}
