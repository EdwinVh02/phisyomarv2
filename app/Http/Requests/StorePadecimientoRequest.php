<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePadecimientoRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'paciente_id'    => 'required|exists:pacientes,id',
            'nombre'         => 'required|string|max:100',
            'descripcion'    => 'nullable|string',
            'fecha_diagnostico' => 'nullable|date',
            'activo'         => 'nullable|boolean',
            // Agrega más campos si tienes
        ];
    }
}
