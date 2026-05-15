<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnigmeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lieu_id' => 'required|exists:lieux,id',
            'type' => 'required|in:force1,force2,force3,enfant',
            'texte' => 'required|string|min:10|max:1000',
            'image_url' => 'nullable|string|url',
        ];
    }
}