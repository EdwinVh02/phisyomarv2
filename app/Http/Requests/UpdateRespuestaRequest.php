<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRespuestaRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'pregunta_id' => 'sometimes|exists:preguntas,id',
            'paciente_id' => 'nullable|exists:pacientes,id',
            'texto'       => 'sometimes|string',
            'valor'       => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'pregunta_id.exists' => 'La pregunta seleccionada no existe.',
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
        ];
    }
}
