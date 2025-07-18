<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePacienteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usuario_id' => 'required|exists:usuarios,id',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|string|max:20',
            'contacto_emergencia_parentesco' => 'nullable|string|max:50',
            'tutor_nombre' => 'nullable|string|max:100',
            'tutor_telefono' => 'nullable|string|max:20',
            'tutor_parentesco' => 'nullable|string|max:50',
            'tutor_direccion' => 'nullable|string|max:150',
            'historial_medico_id' => 'nullable|exists:historial_medicos,id',
            'fecha_nacimiento' => 'nullable|date',
            'curp' => 'nullable|string|max:18',
            'sexo' => 'nullable|in:M,F,O',
            'ocupacion' => 'nullable|string|max:100',
            // Agrega otros campos según tu migración
        ];
    }
}
