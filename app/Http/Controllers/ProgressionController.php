<?php

namespace App\Http\Controllers;

use App\Models\Partie;
use Inertia\Inertia;

class ProgressionController extends Controller
{
    public function getCurrentEnigme(Partie $partie)
    {
        return Inertia::render('Player/Enigme', [
            'partie' => $partie->load('progression.enigmeCourante'),
        ]);
    }

    public function store(Partie $partie)
    {
        return redirect()->back();
    }
}
