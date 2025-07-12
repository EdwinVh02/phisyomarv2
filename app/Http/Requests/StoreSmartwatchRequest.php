<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSmartwatchRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'paciente_id' => 'required|exists:pacientes,id',
            'marca'       => 'required|string|max:50',
            'modelo'      => 'nullable|string|max:50',
            'numero_serie'=> 'nullable|string|max:100',
            'activo'      => 'nullable|boolean',
            // Agrega otros campos que tu migración tenga
        ];
    }
}
