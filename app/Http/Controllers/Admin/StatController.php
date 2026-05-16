<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Partie;
use App\Models\User;
use App\Models\Enigme;

class StatController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_parties' => Partie::count(),
                'total_users' => User::where('is_admin', false)->count(),
                'total_enigmes' => Enigme::count(),
                'parties_en_cours' => Partie::where('statut', 'en_cours')->count(),
            ]
        ]);
    }
}
