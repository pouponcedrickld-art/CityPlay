<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enigme;
use App\Models\Environnement;
use App\Models\Lieu;
use App\Models\Partie;
use App\Models\Team;
use App\Models\User;
use App\Models\Ville;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StatController extends Controller
{
    public function dashboard()
    {
        // Statistiques par ville pour le graphique
        $partiesParVille = DB::table('parties')
            ->join('environnements', 'parties.environnement_id', '=', 'environnements.id')
            ->join('villes', 'environnements.ville_id', '=', 'villes.id')
            ->select('villes.nom', DB::raw('count(*) as total'))
            ->groupBy('villes.nom')
            ->get();

        // Évolutions des inscriptions (7 derniers jours)
        $inscriptionsRecentes = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'villes' => Ville::count(),
                'environnements' => Environnement::count(),
                'lieux' => Lieu::count(),
                'enigmes' => Enigme::count(),
                'users' => User::count(),
                'parties' => Partie::count(),
                'teams' => Team::count(),
            ],
            'charts' => [
                'parties_par_ville' => $partiesParVille,
                'inscriptions_recentes' => $inscriptionsRecentes,
            ]
        ]);
    }
}
