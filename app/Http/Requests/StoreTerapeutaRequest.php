<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerapeutaRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'usuario_id'            => 'required|exists:usuarios,id',
            'cedula_profesional'    => 'required|string|max:30',
            'especialidad_principal'=> 'nullable|string|max:100',
            'experiencia_anios'     => 'nullable|integer|min:0',
            'estatus'               => 'nullable|in:activo,inactivo',
            // Si tu migración tiene relación con clínica, ejemplo:
            // 'clinica_id'         => 'nullable|exists:clinicas,id',
        ];
    }
}
