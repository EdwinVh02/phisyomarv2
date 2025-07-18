<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaquetePacienteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'paciente_id' => 'sometimes|exists:pacientes,id',
            'paquete_id' => 'sometimes|exists:paquete_sesions,id',
            'fecha_adquisicion' => 'nullable|date',
            'estatus' => 'nullable|in:activo,finalizado,cancelado',
        ];
    }

    public function messages()
    {
        return [
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
            'paquete_id.exists' => 'El paquete seleccionado no existe.',
        ];
    }
}
