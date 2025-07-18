<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTratamientoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'required|string|max:100',
            'tarifa_id' => 'required|exists:tarifas,id',
            'descripcion' => 'nullable|string',
            'duracion' => 'nullable|integer|min:1',
            // Más campos según tu migración
        ];
    }
}
