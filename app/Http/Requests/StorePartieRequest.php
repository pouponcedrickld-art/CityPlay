<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'environnement_id' => 'required|exists:environnements,id',
            'mode' => 'required|in:solo,team',
            'nb_joueurs' => 'required|integer|min:1|max:10',
            'duree' => 'required|integer|min:15|max:300', // en minutes
            'locomotion' => 'required|in:pied,velo,voiture',
            'difficulte' => 'required|in:1,2,3',
        ];
    }
}