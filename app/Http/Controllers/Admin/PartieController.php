<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partie;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartieController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Parties/Index', [
            'parties' => Partie::with(['environnement', 'createur', 'team'])
                ->latest()
                ->get(),
        ]);
    }

    public function destroy(Partie $partie)
    {
        $partie->delete();

        return redirect()->route('admin.parties.index')
            ->with('success', 'Partie supprimée avec succès.');
    }
}
