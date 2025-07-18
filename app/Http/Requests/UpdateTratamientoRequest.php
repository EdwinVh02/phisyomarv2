<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTratamientoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'sometimes|string|max:100',
            'tarifa_id' => 'sometimes|exists:tarifas,id',
            'descripcion' => 'nullable|string',
            'duracion' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'tarifa_id.exists' => 'La tarifa seleccionada no existe.',
        ];
    }
}
