<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

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

        try {
            Mail::to($user->email)->send(new OtpMail($code, $user->name));
            Log::info("OTP envoyé par mail à {$user->email}");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de l'OTP à {$user->email} : " . $e->getMessage());
        }

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
