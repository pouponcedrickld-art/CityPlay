<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    /**
     * Gère la requête entrante pour vérifier si l'utilisateur a validé son compte par OTP.
     * 
     * Si l'utilisateur est connecté mais n'a pas de date de vérification OTP (otp_verified_at),
     * il est systématiquement redirigé vers la page de saisie du code OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // On vérifie si l'utilisateur est authentifié et si son compte n'est pas encore vérifié
        if ($user && !$user->otp_verified_at) {
            // Redirection forcée vers la page de vérification OTP avec un message d'information
            return redirect()->route('otp.show')
                ->with('error', 'Veuillez vérifier votre compte avec le code OTP envoyé par mail.');
        }

        // Si tout est en règle, on laisse passer la requête
        return $next($request);
    }
}
