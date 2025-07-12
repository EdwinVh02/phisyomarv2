<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePacienteRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'contacto_emergencia_nombre'     => 'nullable|string|max:100',
            'contacto_emergencia_telefono'   => 'nullable|string|max:20',
            'contacto_emergencia_parentesco' => 'nullable|string|max:50',
            'tutor_nombre'                   => 'nullable|string|max:100',
            'tutor_telefono'                 => 'nullable|string|max:20',
            'tutor_parentesco'               => 'nullable|string|max:50',
            'tutor_direccion'                => 'nullable|string|max:150',
            'historial_medico_id'            => 'nullable|exists:historial_medicos,id',
            'fecha_nacimiento'               => 'nullable|date',
            'curp'                           => 'nullable|string|max:18',
            'sexo'                           => 'nullable|in:M,F,O',
            'ocupacion'                      => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'historial_medico_id.exists' => 'El historial médico no existe.',
            'fecha_nacimiento.date'      => 'La fecha de nacimiento debe ser válida.',
            'curp.max'                  => 'La CURP no debe exceder 18 caracteres.',
            'sexo.in'                   => 'El sexo debe ser M, F u O.',
        ];
    }
}
