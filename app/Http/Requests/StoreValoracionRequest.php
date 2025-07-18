<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreValoracionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'puntuacion' => 'required|integer|min:1|max:5',
            'fecha_hora' => 'required|date',
            'paciente_id' => 'required|exists:pacientes,id',
            'terapeuta_id' => 'nullable|exists:terapeutas,id',
            'comentarios' => 'nullable|string',
            // Otros campos según tu migración
        ];
    }
}
