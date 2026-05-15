<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Teams/Index', [
            'teams' => Team::with(['createur'])
                ->withCount('users')
                ->latest()
                ->get(),
        ]);
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()->route('admin.teams.index')
            ->with('success', 'Équipe supprimée avec succès.');
    }
}
