<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsentimientoInformadoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'paciente_id' => 'sometimes|exists:pacientes,id',
            'fecha_firma' => 'sometimes|date',
            'documento' => 'nullable|string|max:255',
            'firmado_por' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
            'fecha_firma.date' => 'La fecha de firma debe ser válida.',
        ];
    }
}
