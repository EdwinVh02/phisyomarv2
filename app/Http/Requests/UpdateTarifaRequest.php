<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTarifaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'descripcion' => 'sometimes|string|max:255',
            'monto' => 'sometimes|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'descripcion.max' => 'La descripción no debe exceder 255 caracteres.',
        ];
    }
}
