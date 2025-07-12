<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsentimientoInformadoRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'paciente_id'    => 'required|exists:pacientes,id',
            'fecha_firma'    => 'required|date',
            'documento'      => 'required|string|max:255', // puede ser la ruta o base64 o texto del documento
            'firmado_por'    => 'nullable|string|max:100',
            'observaciones'  => 'nullable|string',
            // Agrega otros campos que estén en tu migración
        ];
    }
}
