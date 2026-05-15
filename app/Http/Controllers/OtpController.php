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
     * Afficher la page de vérification OTP
     */
    public function show()
    {
        $user = auth()->user();
        // Si déjà vérifié, rediriger vers le dashboard approprié
        if ($this->otpService->estVerifie($user)) {
            if ($user->is_admin || $user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/OTPVerify');
    }

    /**
     * Vérifier le code OTP soumis
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();
        $valide = $this->otpService->verifier($user, $request->code);

        if (!$valide) {
            return back()->withErrors([
                'code' => 'Code OTP incorrect. Veuillez réessayer.'
            ]);
        }

        if ($user->is_admin || $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Compte admin vérifié avec succès !');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Compte vérifié avec succès !');
    }

    /**
     * Renvoyer un nouveau code OTP
     */
    public function resend()
    {
        $user = auth()->user();
        $this->otpService->generer($user);

        return back()->with('info', 'Un nouveau code a été envoyé. (Mode mock : voir les logs)');
    }
}