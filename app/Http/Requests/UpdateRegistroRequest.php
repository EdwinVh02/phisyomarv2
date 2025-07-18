<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRegistroRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'historial_medico_id' => 'sometimes|exists:historial_medicos,id',
            'fecha_hora' => 'nullable|date',
            'antecedentes' => 'nullable|string',
            'medicacion_actual' => 'nullable|string',
            'postura' => 'nullable|string',
            'marcha' => 'nullable|string',
            'fuerza_muscular' => 'nullable|string',
            'rango_movimiento_muscular_rom' => 'nullable|string',
            'tono_muscular' => 'nullable|string',
            'localizacion_dolor' => 'nullable|string',
            'intensidad_dolor' => 'nullable|integer|min:0|max:10',
            'tipo_dolor' => 'nullable|string|max:100',
            'movilidad_articular' => 'nullable|string',
            'balance_y_coordinacion' => 'nullable|string',
            'sensibilidad' => 'nullable|string',
            'reflejos_osteotendinosos' => 'nullable|string',
            'motivo_visita' => 'nullable|string',
            'numero_sesion' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'historial_medico_id.exists' => 'El historial médico seleccionado no existe.',
        ];
    }
}
