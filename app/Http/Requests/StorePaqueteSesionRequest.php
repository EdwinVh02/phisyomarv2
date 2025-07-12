<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaqueteSesionRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'nombre'           => 'required|string|max:100',
            'numero_sesiones'  => 'required|integer|min:1',
            'descuento'        => 'nullable|numeric|min:0|max:100',
            'descripcion'      => 'nullable|string',
            // Agrega otros campos si tienes
        ];
    }
}
