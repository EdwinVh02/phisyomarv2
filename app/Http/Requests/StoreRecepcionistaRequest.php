<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecepcionistaRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'usuario_id' => 'required|exists:usuarios,id',
            'turno'      => 'nullable|string|max:30',
            // agrega más campos si tu migración lo requiere
        ];
    }
}
