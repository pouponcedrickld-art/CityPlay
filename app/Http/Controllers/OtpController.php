<?php

namespace App\Http\Controllers;

use App\Services\OtpService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OtpController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Afficher la page de vérification OTP.
     * 
     * Cette méthode vérifie d'abord si l'utilisateur n'est pas déjà vérifié 
     * pour éviter les accès inutiles à la page de saisie du code.
     */
    public function show()
    {
        $user = auth()->user();
        
        // Si le compte est déjà vérifié, on redirige vers le tableau de bord approprié selon le rôle
        if ($this->otpService->estVerifie($user)) {
            if ($user->is_admin || $user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('dashboard');
        }

        // Sinon, on affiche la vue de saisie du code OTP (Inertia)
        return Inertia::render('Auth/OTPVerify');
    }

    /**
     * Vérifier le code OTP soumis par l'utilisateur.
     * 
     * Valide le format du code (6 chiffres) et interroge le service OtpService
     * pour confirmer la validité temporelle et numérique du code.
     */
    public function verify(Request $request)
    {
        // Validation stricte du format (6 caractères requis)
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        // Appel au service métier pour la vérification
        $valide = $this->otpService->verifier($user, $request->code);

        if (!$valide) {
            // Retour avec erreur si le code est expiré ou erroné
            return back()->withErrors([
                'code' => 'Code OTP incorrect ou expiré. Veuillez réessayer.'
            ]);
        }

        // Une fois vérifié, redirection vers l'espace de jeu ou d'administration
        if ($user->is_admin || $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Compte admin vérifié avec succès !');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Compte vérifié avec succès !');
    }

    /**
     * Renvoyer un nouveau code OTP par email.
     * 
     * Utile si l'utilisateur n'a pas reçu le premier mail ou si le code a expiré.
     */
    public function resend()
    {
        $user = auth()->user();
        // Génération et envoi d'un nouveau code via le service
        $this->otpService->generer($user);

        return back()->with('info', 'Un nouveau code a été envoyé à votre adresse email.');
    }
}