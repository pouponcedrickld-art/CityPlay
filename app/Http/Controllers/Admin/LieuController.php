<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Environnement;
use App\Models\Lieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LieuController extends Controller
{
    public function index(Environnement $environnement): Response
    {
        return Inertia::render('Admin/Lieux/Index', [
            'environnement' => $environnement->load('ville'),
            'lieux'         => $environnement->lieux()->withCount('enigmes')->get(),
        ]);
    }

    public function create(Environnement $environnement): Response
    {
        return Inertia::render('Admin/Lieux/Form', [
            'environnement' => $environnement->load('ville'),
            'lieu'          => null,
        ]);
    }

    public function store(Request $request, Environnement $environnement)
    {
        $data = $request->validate([
            'nom'           => 'required|string|max:150',
            'ordre'         => 'required|integer|min:0',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'rayon_metres'  => 'required|integer|min:5|max:500',
            'description'   => 'nullable|string|max:500',
            'photos'        => 'nullable|array|max:4',
            'photos.*'      => 'image|max:2048',
        ]);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('lieux/photos', 'public');
            }
        }

        $environnement->lieux()->create(array_merge(
            $data,
            ['photos' => $photoPaths]
        ));

        return redirect()->route('admin.lieux.index', $environnement)
            ->with('success', 'Lieu créé avec succès.');
    }

    public function edit(Environnement $environnement, Lieu $lieu): Response
    {
        return Inertia::render('Admin/Lieux/Form', [
            'environnement' => $environnement->load('ville'),
            'lieu'          => $lieu,
        ]);
    }

    public function update(Request $request, Environnement $environnement, Lieu $lieu)
    {
        $data = $request->validate([
            'nom'           => 'required|string|max:150',
            'ordre'         => 'required|integer|min:0',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'rayon_metres'  => 'required|integer|min:5|max:500',
            'description'   => 'nullable|string|max:500',
            'photos'        => 'nullable|array|max:4',
            'photos.*'      => 'image|max:2048',
            'existing_photos' => 'nullable|array',
        ]);

        $photoPaths = $data['existing_photos'] ?? [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('lieux/photos', 'public');
            }
        }

        $removedPhotos = array_diff($lieu->photos ?? [], $photoPaths);
        foreach ($removedPhotos as $path) {
            Storage::disk('public')->delete($path);
        }

        $lieu->update(array_merge(
            $data,
            ['photos' => $photoPaths]
        ));

        return redirect()->route('admin.lieux.index', $environnement)
            ->with('success', 'Lieu mis à jour.');
    }

    public function destroy(Environnement $environnement, Lieu $lieu)
    {
        foreach ($lieu->photos ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }

        $lieu->delete();

        return redirect()->route('admin.lieux.index', $environnement)
            ->with('success', 'Lieu supprimé.');
    }

    public function reorder(Request $request, Environnement $environnement)
    {
        $request->validate([
            'ordre' => 'required|array',
            'ordre.*' => 'integer|exists:lieux,id',
        ]);

        foreach ($request->ordre as $position => $lieuId) {
            Lieu::where('id', $lieuId)
                ->where('environnement_id', $environnement->id)
                ->update(['ordre' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }
}
