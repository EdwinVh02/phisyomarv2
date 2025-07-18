<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBitacoraRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usuario_id' => 'sometimes|exists:usuarios,id',
            'accion' => 'nullable|string|max:255',
            'tabla' => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
            'accion.max' => 'La acción no debe exceder 255 caracteres.',
            'tabla.max' => 'El nombre de la tabla no debe exceder 50 caracteres.',
        ];
    }
}
