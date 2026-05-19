<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Models\AppInvitation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function create(Request $request): Response
    {
        $invitation = null;
        if ($request->has('invite')) {
            $invitation = AppInvitation::where('token', $request->invite)
                ->whereNull('used_at')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->first();
        }

        return Inertia::render('Auth/Register', [
            'invitation_token' => $invitation?->token,
            'prefilled_email' => $invitation?->email,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pseudo' => 'required|string|max:100|unique:users,pseudo',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'consent_cgu' => 'required|accepted',
            'consent_donnees' => 'required|accepted',
            'invitation_token' => 'required|string|exists:app_invitations,token',
        ]);

        // Vérifier à nouveau la validité du token
        $invitation = AppInvitation::where('token', $request->invitation_token)
            ->whereNull('used_at')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$invitation) {
            return back()->withErrors(['invitation_token' => 'Ce lien d\'invitation est invalide ou expiré.']);
        }

        // Si l'invitation était restreinte à un email
        if ($invitation->email && strtolower($invitation->email) !== strtolower($request->email)) {
            return back()->withErrors(['email' => 'Cet email ne correspond pas à celui de l\'invitation.']);
        }

        $user = User::create([
            'name' => $request->name,
            'pseudo' => $request->pseudo,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'consent_cgu' => true,
            'consent_donnees' => true,
        ]);

        // Marquer l'invitation comme utilisée
        $invitation->update([
            'used_at' => now(),
            'used_by' => $user->id,
        ]);

        // Générer et logger le code OTP (mock)
        $this->otpService->generer($user);

        event(new Registered($user));

        Auth::login($user);

        // Rediriger vers la page de vérification OTP avec un message flash
        return redirect()->route('otp.show')->with('success', 'Votre compte a été créé avec succès ! Veuillez vérifier votre code OTP.');
    }
}