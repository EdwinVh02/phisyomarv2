<?php

namespace App\Http\Requests;

use App\Rules\CitaDisponible;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCitaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $citaId = $this->route('cita') ? $this->route('cita')->id : null;

        return [
            'fecha_hora' => [
                'sometimes',
                'date',
                'after:now',
                new CitaDisponible(
                    $this->input('terapeuta_id') ?? $this->route('cita')->terapeuta_id,
                    $this->input('duracion', 60),
                    $citaId
                ),
            ],
            'paciente_id' => 'sometimes|exists:pacientes,id',
            'terapeuta_id' => 'sometimes|exists:terapeutas,id',
            'estado' => 'nullable|in:agendada,atendida,cancelada,no_asistio,reprogramada',
            'tipo' => 'sometimes|string|max:50',
            'duracion' => 'nullable|integer|min:15|max:240',
            'ubicacion' => 'nullable|string|max:100',
            'equipo_asignado' => 'nullable|string|max:100',
            'motivo' => 'sometimes|string',
            'observaciones' => 'nullable|string',
            'escala_dolor_eva_inicio' => 'nullable|integer|min:0|max:10',
            'escala_dolor_eva_fin' => 'nullable|integer|min:0|max:10',
            'como_fue_lesion' => 'nullable|string',
            'antecedentes_patologicos' => 'nullable|string',
            'antecedentes_no_patologicos' => 'nullable|string',
            'paquete_paciente_id' => 'nullable|exists:paquete_pacientes,id',
            'registro_id' => 'nullable|exists:registros,id',
        ];
    }

    public function messages()
    {
        return [
            'fecha_hora.date' => 'La fecha y hora debe ser una fecha válida.',
            'fecha_hora.after' => 'La fecha y hora debe ser futura.',
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
            'terapeuta_id.exists' => 'El terapeuta seleccionado no existe.',
            'estado.in' => 'El estado no es válido.',
            'tipo.max' => 'El tipo de cita no puede exceder 50 caracteres.',
            'duracion.integer' => 'La duración debe ser un número entero.',
            'duracion.min' => 'La duración mínima es de 15 minutos.',
            'duracion.max' => 'La duración máxima es de 240 minutos.',
            'escala_dolor_eva_inicio.integer' => 'La escala de dolor inicial debe ser un número entero.',
            'escala_dolor_eva_inicio.min' => 'La escala de dolor inicial debe ser mínimo 0.',
            'escala_dolor_eva_inicio.max' => 'La escala de dolor inicial debe ser máximo 10.',
            'escala_dolor_eva_fin.integer' => 'La escala de dolor final debe ser un número entero.',
            'escala_dolor_eva_fin.min' => 'La escala de dolor final debe ser mínimo 0.',
            'escala_dolor_eva_fin.max' => 'La escala de dolor final debe ser máximo 10.',
        ];
    }
}
