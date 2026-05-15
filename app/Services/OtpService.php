<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Générer et sauvegarder un code OTP pour l'utilisateur
     */
    public function generer(User $user): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp_code' => $code,
            'otp_verified_at' => null, // reset
        ]);

        // MOCK : en prod, on enverrait un vrai email/SMS ici
        Log::info("OTP pour {$user->email} : {$code}");

        return $code;
    }

    /**
     * Vérifier le code OTP saisi par l'utilisateur
     */
    public function verifier(User $user, string $codeSaisi): bool
    {
        if ($user->otp_code !== $codeSaisi) {
            return false;
        }

        $user->update([
            'otp_verified_at' => now(),
            'otp_code' => null, // on efface le code après usage
        ]);

        return true;
    }

    /**
     * Vérifier si l'utilisateur a déjà validé son OTP
     */
    public function estVerifie(User $user): bool
    {
        return $user->otp_verified_at !== null;
    }
}