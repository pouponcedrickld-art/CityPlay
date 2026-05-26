<?php

namespace App\Services;

use App\Models\User;

class GamingService
{
    /**
     * Taux de conversion : 100 points = 10 CityCoins
     * Ce qui équivaut à 1 CityCoin pour 10 points.
     */
    const POINTS_PER_COIN = 10;

    /**
     * Convertit des points en CityCoins et les ajoute au solde de l'utilisateur.
     *
     * @param User $user
     * @param int $points
     * @return int Le nombre de coins ajoutés
     */
    public function awardCoinsFromPoints(User $user, int $points): int
    {
        $coins = (int) floor($points / self::POINTS_PER_COIN);
        
        if ($coins > 0) {
            $user->increment('balance_coins', $coins);
        }

        return $coins;
    }

    /**
     * Calcule le solde de coins correspondant à un montant de points (sans attribution).
     */
    public function calculateCoins(int $points): int
    {
        return (int) floor($points / self::POINTS_PER_COIN);
    }
}
