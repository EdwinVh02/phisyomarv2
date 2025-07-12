<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCitaRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'fecha_hora'    => 'sometimes|date',
            'paciente_id'   => 'sometimes|exists:pacientes,id',
            'terapeuta_id'  => 'sometimes|exists:terapeutas,id',
            'estado'        => 'nullable|in:Agendada,Realizada,Cancelada',
            'tipo'          => 'nullable|string|max:50',
            'duracion'      => 'nullable|integer',
            'ubicacion'     => 'nullable|string|max:255',
            'notas'         => 'nullable|string',
            'motivo'        => 'nullable|string|max:255',
            'diagnostico'   => 'nullable|string',
            'tratamiento_id'=> 'nullable|exists:tratamientos,id',
        ];
    }

    public function messages()
    {
        return [
            'fecha_hora.date'              => 'La fecha y hora debe ser una fecha válida.',
            'paciente_id.exists'           => 'El paciente seleccionado no existe.',
            'terapeuta_id.exists'          => 'El terapeuta seleccionado no existe.',
            'estado.in'                    => 'El estado no es válido.',
            'tratamiento_id.exists'        => 'El tratamiento seleccionado no existe.',
        ];
    }
}
