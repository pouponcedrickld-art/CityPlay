<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enigme;
use App\Models\Lieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EnigmeController extends Controller
{
    /**
     * Affiche les 4 énigmes d'un lieu (une par type)
     */
    public function index(Lieu $lieu): Response
    {
        $enigmes = $lieu->enigmes->keyBy('type'); // Indexées par type pour affichage facile

        return Inertia::render('Admin/Enigmes/Index', [
            'lieu'    => $lieu->load('environnement.ville'),
            'enigmes' => $enigmes,
            'types'   => Enigme::TYPE_LABELS,
        ]);
    }

    /**
     * Formulaire créer/modifier une énigme
     */
    public function edit(Lieu $lieu, string $type): Response
    {
        abort_unless(in_array($type, Enigme::TYPES), 404);

        $enigme = $lieu->enigmes()->where('type', $type)->first();

        return Inertia::render('Admin/Enigmes/Form', [
            'lieu'   => $lieu->load('environnement'),
            'enigme' => $enigme,
            'type'   => $type,
            'typeLabel' => Enigme::TYPE_LABELS[$type],
        ]);
    }

    /**
     * Créer ou mettre à jour une énigme (upsert par lieu + type)
     */
    public function upsert(Request $request, Lieu $lieu, string $type)
    {
        abort_unless(in_array($type, Enigme::TYPES), 404);

        $data = $request->validate([
            'texte'       => 'required|string|max:2000',
            'image'       => 'nullable|image|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        $enigme = $lieu->enigmes()->where('type', $type)->first();
        $imageUrl = $enigme?->image_url;

        // Supprimer image si demandé ou si nouvelle image uploadée
        if (($data['remove_image'] ?? false) || $request->hasFile('image')) {
            if ($imageUrl) {
                Storage::disk('public')->delete($imageUrl);
                $imageUrl = null;
            }
        }

        if ($request->hasFile('image')) {
            $imageUrl = $request->file('image')->store('enigmes/images', 'public');
        }

        $lieu->enigmes()->updateOrCreate(
            ['lieu_id' => $lieu->id, 'type' => $type],
            [
                'texte'      => $data['texte'],
                'image_url' => $imageUrl,
            ]
        );

        return redirect()->route('admin.enigmes.index', $lieu)
            ->with('success', 'Énigme "' . Enigme::TYPE_LABELS[$type] . '" enregistrée.');
    }

    public function destroy(Lieu $lieu, string $type)
    {
        abort_unless(in_array($type, Enigme::TYPES), 404);

        $enigme = $lieu->enigmes()->where('type', $type)->firstOrFail();

        if ($enigme->image_url) {
            Storage::disk('public')->delete($enigme->image_url);
        }

        $enigme->delete();

        return redirect()->route('admin.enigmes.index', $lieu)
            ->with('success', 'Énigme supprimée.');
    }
}
