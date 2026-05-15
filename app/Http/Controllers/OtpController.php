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
        // Si déjà vérifié, rediriger vers dashboard
        if ($this->otpService->estVerifie(auth()->user())) {
            return redirect()->route('player.dashboard');
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

        return redirect()->route('player.dashboard')
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