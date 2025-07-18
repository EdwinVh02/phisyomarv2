<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHistorialMedicoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'paciente_id' => 'required|exists:pacientes,id',
            'observaciones' => 'nullable|string',
            'alergias' => 'nullable|string|max:255',
            'antecedentes' => 'nullable|string',
            'fecha_ultimo_reporte' => 'nullable|date',
            // Agrega otros campos clínicos según tu migración
        ];
    }
}
