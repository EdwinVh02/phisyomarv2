<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHistorialMedicoRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'paciente_id'          => 'sometimes|exists:pacientes,id',
            'observaciones'        => 'nullable|string',
            'alergias'             => 'nullable|string|max:255',
            'antecedentes'         => 'nullable|string',
            'fecha_ultimo_reporte' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
        ];
    }
}
