<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        \Illuminate\Support\Facades\Log::info('Admin access check', [
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'has_role_admin' => $user->hasRole('admin')
        ]);

        if (!$user->is_admin && !$user->hasRole('admin')) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
