<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Environnement;
use App\Models\Ville;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EnvironnementController extends Controller
{
    // ──────────────────────────────────────────────
    //  VILLES
    // ──────────────────────────────────────────────

    public function indexVilles(): Response
    {
        return Inertia::render('Admin/Villes/Index', [
            'villes' => Ville::withCount('environnements')->latest()->get(),
        ]);
    }

    public function storeVille(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:150|unique:villes,nom',
        ]);

        $data['slug'] = Str::slug($data['nom']);

        Ville::create($data);

        return redirect()->route('admin.villes.index')
            ->with('success', 'Ville créée avec succès.');
    }

    public function updateVille(Request $request, Ville $ville)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:150|unique:villes,nom,' . $ville->id,
        ]);

        $data['slug'] = Str::slug($data['nom']);

        $ville->update($data);

        return redirect()->route('admin.villes.index')
            ->with('success', 'Ville mise à jour.');
    }

    public function destroyVille(Ville $ville)
    {
        $ville->delete();

        return redirect()->route('admin.villes.index')
            ->with('success', 'Ville supprimée.');
    }

    // ──────────────────────────────────────────────
    //  ENVIRONNEMENTS
    // ──────────────────────────────────────────────

    public function index(): Response
    {
        return Inertia::render('Admin/Environnements/Index', [
            'environnements' => Environnement::with('ville')
                ->withCount('lieux')
                ->latest()
                ->get(),
            'villes' => Ville::orderBy('nom')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Environnements/Form', [
            'villes'       => Ville::orderBy('nom')->get(),
            'environnement' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ville_id'                  => 'required|exists:villes,id',
            'nom'                       => 'required|string|max:150',
            'retention_profils_jours'   => 'required|integer|in:30,90,180,365',
            'regles'                    => 'nullable|string|max:2000',
            'duree_vie_lien_heures'     => 'required|integer|min:1|max:168',
            'message_bonne_reponse'     => 'nullable|string|max:500',
            'message_mauvaise_reponse'  => 'nullable|string|max:500',
            'message_fin'               => 'nullable|string|max:500',
            'message_pause'             => 'nullable|string|max:500',
            'lien_restauration'         => 'nullable|url|max:255',
            'lien_boutique'             => 'nullable|url|max:255',
            'lien_notation'             => 'nullable|url|max:255',
        ]);

        Environnement::create($data);

        return redirect()->route('admin.environnements.index')
            ->with('success', 'Environnement créé avec succès.');
    }

    public function edit(Environnement $environnement): Response
    {
        return Inertia::render('Admin/Environnements/Form', [
            'villes'        => Ville::orderBy('nom')->get(),
            'environnement' => $environnement->load('ville'),
        ]);
    }

    public function update(Request $request, Environnement $environnement)
    {
        $data = $request->validate([
            'ville_id'                  => 'required|exists:villes,id',
            'nom'                       => 'required|string|max:150',
            'retention_profils_jours'   => 'required|integer|in:30,90,180,365',
            'regles'                    => 'nullable|string|max:2000',
            'duree_vie_lien_heures'     => 'required|integer|min:1|max:168',
            'message_bonne_reponse'     => 'nullable|string|max:500',
            'message_mauvaise_reponse'  => 'nullable|string|max:500',
            'message_fin'               => 'nullable|string|max:500',
            'message_pause'             => 'nullable|string|max:500',
            'lien_restauration'         => 'nullable|url|max:255',
            'lien_boutique'             => 'nullable|url|max:255',
            'lien_notation'             => 'nullable|url|max:255',
        ]);

        $environnement->update($data);

        return redirect()->route('admin.environnements.index')
            ->with('success', 'Environnement mis à jour.');
    }

    public function destroy(Environnement $environnement)
    {
        $environnement->delete();

        return redirect()->route('admin.environnements.index')
            ->with('success', 'Environnement supprimé.');
    }
}
