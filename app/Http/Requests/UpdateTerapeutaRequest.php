<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTerapeutaRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'usuario_id'            => 'sometimes|exists:usuarios,id',
            'cedula_profesional'    => 'sometimes|string|max:30',
            'especialidad_principal'=> 'nullable|string|max:100',
            'experiencia_anios'     => 'nullable|integer|min:0',
            'estatus'               => 'nullable|in:activo,inactivo',
            // 'clinica_id'         => 'nullable|exists:clinicas,id',
        ];
    }

    public function messages()
    {
        return [
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
        ];
    }
}
