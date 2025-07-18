<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecepcionistaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usuario_id' => 'sometimes|exists:usuarios,id',
        ];
    }

    public function messages()
    {
        return [
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
        ];
    }
}
