<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnvironnementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Tu ajouteras la vérification admin plus tard
    }

    public function rules(): array
    {
        return [
            'ville_id' => 'required|exists:villes,id',
            'nom' => 'required|string|max:150',
            'retention_profils_jours' => 'required|integer|min:1|max:365',
            'duree_vie_lien_heures' => 'required|integer|min:1|max:168',
            'messages' => 'nullable|array',
            'regles' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.max' => 'Le nom ne doit pas dépasser 150 caractères.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
        ];
    }
}