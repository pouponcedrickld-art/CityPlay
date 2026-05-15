<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enigme;
use App\Models\Lieu;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnigmeController extends Controller
{
    /**
     * Affiche la liste des énigmes.
     */
    public function index()
    {
        return Inertia::render('Admin/Enigmes/Index', [
            // On récupère les énigmes avec la relation lieu pour l'affichage
            'enigmes' => Enigme::with('lieu')->latest()->get(),
        ]);
    }

    /**
     * Affiche le formulaire de création d'une nouvelle énigme.
     */
    public function create()
    {
        return Inertia::render('Admin/Enigmes/Create', [
            // On envoie la liste des lieux pour le select du formulaire
            'lieux' => Lieu::select('id', 'nom')->get(),
            'types' => ['force1', 'force2', 'force3', 'enfant']
        ]);
    }

    /**
     * Enregistre une nouvelle énigme en base de données.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'lieu_id'   => 'required|exists:lieux,id',
            'type'      => 'required|in:force1,force2,force3,enfant',
            'titre'     => 'required|string|max:255',
            'texte'     => 'required|string|min:5',
            'points'    => 'required|integer|min:0',
            'image_url' => 'nullable|url',
            'actif'     => 'boolean',
        ]);

        Enigme::create($data);

        return redirect()->route('enigmes.index')->with('success', 'Énigme créée avec succès !');
    }

    /**
     * Affiche le formulaire d'édition d'une énigme existante.
     */
    public function edit(Enigme $enigme)
    {
        return Inertia::render('Admin/Enigmes/Edit', [
            'enigme' => $enigme,
            'lieux'  => Lieu::select('id', 'nom')->get(),
            'types'  => ['force1', 'force2', 'force3', 'enfant']
        ]);
    }

    /**
     * Met à jour une énigme en base de données.
     */
    public function update(Request $request, Enigme $enigme)
    {
        $data = $request->validate([
            'lieu_id'   => 'required|exists:lieux,id',
            'type'      => 'required|in:force1,force2,force3,enfant',
            'titre'     => 'required|string|max:255',
            'texte'     => 'required|string|min:5',
            'points'    => 'required|integer|min:0',
            'image_url' => 'nullable|url',
            'actif'     => 'boolean',
        ]);

        $enigme->update($data);

        return redirect()->route('enigmes.index')->with('success', 'Énigme mise à jour avec succès !');
    }

    /**
     * Supprime une énigme de la base de données.
     */
    public function destroy(Enigme $enigme)
    {
        $enigme->delete();

        return redirect()->route('enigmes.index')->with('success', 'Énigme supprimée avec succès !');
    }
}
