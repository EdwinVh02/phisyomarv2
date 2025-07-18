<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClinicaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'sometimes|string|max:100',
            'direccion' => 'sometimes|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
            'responsable' => 'nullable|string|max:100',
            'rfc' => 'nullable|string|max:15',
            'horario' => 'nullable|string|max:100',
            'logo' => 'nullable|string|max:255',
            'sitio_web' => 'nullable|url|max:150',
        ];
    }

    public function messages()
    {
        return [
            'nombre.max' => 'El nombre no debe exceder 100 caracteres.',
            'direccion.max' => 'La dirección no debe exceder 255 caracteres.',
            'correo.email' => 'El correo electrónico no es válido.',
            'logo.max' => 'El logo no debe exceder 255 caracteres.',
            'sitio_web.url' => 'El sitio web debe ser una URL válida.',
        ];
    }
}
