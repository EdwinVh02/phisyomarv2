<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdministradorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usuario_id' => 'sometimes|exists:usuarios,id',
            'area'       => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
            'area.max'          => 'El área no debe exceder 100 caracteres.',
        ];
    }
}
