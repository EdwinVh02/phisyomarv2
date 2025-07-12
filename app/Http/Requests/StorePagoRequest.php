<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'paciente_id' => 'required|exists:pacientes,id',
            'monto'       => 'required|numeric|min:0',
            'metodo'      => 'required|string|max:50',
            'fecha'       => 'nullable|date',
            'referencia'  => 'nullable|string|max:100',
            'estatus'     => 'nullable|in:pendiente,aprobado,rechazado',
            // Agrega otros campos de tu migración
        ];
    }
}
