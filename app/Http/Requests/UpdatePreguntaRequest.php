<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreguntaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'encuesta_id' => 'sometimes|exists:encuestas,id',
            'texto' => 'sometimes|string|max:255',
            'tipo' => 'nullable|string|max:50',
            'orden' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'encuesta_id.exists' => 'La encuesta seleccionada no existe.',
            'texto.max' => 'El texto no debe exceder 255 caracteres.',
        ];
    }
}
