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

        // Mise à jour immédiate en base de données
        // Chaque nouvel appel écrase l'ancien code, rendant les mails précédents invalides
        $user->update([
            'otp_code' => $code,
            'otp_expires_at' => now()->addMinutes(15),
            'otp_verified_at' => null,
        ]);

        Log::info("Nouveau code OTP généré et enregistré en base pour {$user->email}. L'ancien code est désormais invalide.");

        try {
            Mail::to($user->email)->send(new OtpMail($code, $user->name));
            Log::info("OTP envoyé par mail à {$user->email}. Code: {$code}");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de l'OTP à {$user->email} : " . $e->getMessage() . ". Code généré: {$code}");
        }

        return $code;
    }

    /**
     * Vérifier le code OTP saisi par l'utilisateur
     */
    public function verifier(User $user, string $codeSaisi): bool
    {
        Log::info("Tentative de vérification OTP pour {$user->email}", [
            'code_saisi' => $codeSaisi,
            'code_attendu' => $user->otp_code,
            'expire_at' => $user->otp_expires_at ? $user->otp_expires_at->toDateTimeString() : 'null'
        ]);

        // 1. Vérifier si un code est bien présent en base
        if (!$user->otp_code || !$user->otp_expires_at) {
            Log::warning("Vérification OTP échouée : pas de code ou de date d'expiration en base pour {$user->email}");
            return false;
        }

        // 2. Vérifier si le code correspond
        if ($user->otp_code !== $codeSaisi) {
            Log::warning("Vérification OTP échouée : code incorrect pour {$user->email}");
            return false;
        }

        // 3. Vérifier si le code a expiré
        if ($user->otp_expires_at->isPast()) {
            Log::warning("Vérification OTP échouée : code expiré pour {$user->email} (expirait à {$user->otp_expires_at})");
            return false;
        }

        // 4. Marquer comme vérifié et nettoyer
        $user->update([
            'otp_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        Log::info("Vérification OTP réussie pour {$user->email}");
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
