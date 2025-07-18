<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaqueteSesionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'sometimes|string|max:100',
            'numero_sesiones' => 'sometimes|integer|min:1',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'descripcion' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'nombre.max' => 'El nombre no debe exceder 100 caracteres.',
            'numero_sesiones.min' => 'Debe haber al menos una sesión.',
            'descuento.numeric' => 'El descuento debe ser un número.',
        ];
    }
}
