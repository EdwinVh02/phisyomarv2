<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateValoracionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'puntuacion' => 'sometimes|integer|min:1|max:5',
            'fecha_hora' => 'sometimes|date',
            'paciente_id' => 'sometimes|exists:pacientes,id',
            'terapeuta_id' => 'nullable|exists:terapeutas,id',
            'comentarios' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
            'terapeuta_id.exists' => 'El terapeuta seleccionado no existe.',
        ];
    }
}
