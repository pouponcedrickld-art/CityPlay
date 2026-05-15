<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLieuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'environnement_id' => 'required|exists:environnements,id',
            'nom' => 'required|string|max:150',
            'ordre' => 'required|integer|min:0',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'rayon_metres' => 'required|integer|min:10|max:500',
            'description' => 'required|string|max:500',
        ];
    }
}