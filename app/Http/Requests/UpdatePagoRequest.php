<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePagoRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'paciente_id' => 'sometimes|exists:pacientes,id',
            'monto'       => 'sometimes|numeric|min:0',
            'metodo'      => 'sometimes|string|max:50',
            'fecha'       => 'nullable|date',
            'referencia'  => 'nullable|string|max:100',
            'estatus'     => 'nullable|in:pendiente,aprobado,rechazado',
        ];
    }

    public function messages()
    {
        return [
            'paciente_id.exists' => 'El paciente seleccionado no existe.',
            'monto.numeric'      => 'El monto debe ser un número.',
        ];
    }
}
