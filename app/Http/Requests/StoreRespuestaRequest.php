<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRespuestaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'pregunta_id' => 'required|exists:preguntas,id',
            'paciente_id' => 'nullable|exists:pacientes,id',
            'texto' => 'required|string',
            'valor' => 'nullable|integer',
            // Agrega más campos según tu migración
        ];
    }
}
