<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaquetePacienteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'paciente_id' => 'required|exists:pacientes,id',
            'paquete_id' => 'required|exists:paquete_sesions,id',
            'fecha_adquisicion' => 'required|date',
            'estatus' => 'nullable|in:activo,finalizado,cancelado',
            // Agrega otros campos según tu migración
        ];
    }
}
