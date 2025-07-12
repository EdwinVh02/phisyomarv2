<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePadecimientoRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'paciente_id'        => 'sometimes|exists:pacientes,id',
            'nombre'             => 'sometimes|string|max:100',
            'descripcion'        => 'nullable|string',
            'fecha_diagnostico'  => 'nullable|date',
            'activo'             => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
        ];
    }
}
