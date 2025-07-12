<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEspecialidadRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'nombre'        => 'required|string|max:100|unique:especialidads,nombre',
            'descripcion'   => 'nullable|string',
            // Agrega otros campos si aplica
        ];
    }
}
