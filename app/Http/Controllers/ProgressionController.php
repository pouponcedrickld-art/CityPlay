<?php

namespace App\Http\Controllers;

use App\Models\Partie;
use App\Models\Progression;
use App\Models\Enigme;
use Inertia\Inertia;

class ProgressionController extends Controller
{
    public function getCurrentEnigme(Partie $partie)
    {
        $progression = $partie->progression;

        if (!$progression) {
            return redirect()->route('parties.show', $partie)
                ->with('error', 'La partie n\'a pas encore démarré.');
        }

        $enigme = Enigme::find($progression->enigme_courante_id);

        return Inertia::render('Player/Enigme', [
            'partie' => $partie,
            'enigme' => $enigme,
            'progression' => $progression,
        ]);
    }

    public function show(Partie $partie)
    {
        $progression = $partie->progression;

        return Inertia::render('Player/Progression', [
            'partie' => $partie,
            'progression' => $progression,
        ]);
    }
}
