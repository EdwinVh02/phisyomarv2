<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePreguntaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'encuesta_id' => 'required|exists:encuestas,id',
            'texto' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:50', // ej: 'opcion_multiple', 'abierta'
            'orden' => 'nullable|integer|min:1',
            // Agrega más campos según tu migración
        ];
    }
}
