<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClinicaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'required|string|max:100',
            'direccion' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
            'responsable' => 'nullable|string|max:100',
            'rfc' => 'nullable|string|max:15',
            'horario' => 'nullable|string|max:100',
            'logo' => 'nullable|string|max:255', // si guardas ruta/logo de la clínica
            'sitio_web' => 'nullable|url|max:150',
            // Agrega aquí cualquier otro campo relevante de tu migración
        ];
    }
}
