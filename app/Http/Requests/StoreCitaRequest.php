<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCitaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'fecha_hora'    => 'required|date',
            'paciente_id'   => 'required|exists:pacientes,id',
            'terapeuta_id'  => 'required|exists:terapeutas,id',
            'estado'        => 'required|in:Agendada,Realizada,Cancelada',
            'tipo'          => 'nullable|string|max:50',
            'duracion'      => 'nullable|integer',
            'ubicacion'     => 'nullable|string|max:255',
            'notas'         => 'nullable|string',
            'motivo'        => 'nullable|string|max:255',
            'diagnostico'   => 'nullable|string',
            'tratamiento_id' => 'nullable|exists:tratamientos,id',
            // Agrega aquí cualquier campo adicional que uses en tu migración
        ];
    }
}
