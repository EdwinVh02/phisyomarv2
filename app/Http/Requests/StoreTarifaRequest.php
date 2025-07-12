<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTarifaRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'descripcion' => 'required|string|max:255',
            'monto'       => 'required|numeric|min:0',
            // Agrega otros campos si tienes en tu migración
        ];
    }
}
