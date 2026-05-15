<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
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

    public function create(): Response
    {
        return Inertia::render('Auth/Register');
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
        ]);

        $user = User::create([
            'name' => $request->name,
            'pseudo' => $request->pseudo,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'consent_cgu' => true,
            'consent_donnees' => true,
        ]);

        // Générer et logger le code OTP (mock)
        $this->otpService->generer($user);

        event(new Registered($user));

        Auth::login($user);

        // Rediriger vers la page de vérification OTP avec un message flash
        return redirect()->route('otp.show')->with('success', 'Votre compte a été créé avec succès ! Veuillez vérifier votre code OTP.');
    }
}