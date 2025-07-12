<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecepcionistaRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'usuario_id' => 'sometimes|exists:usuarios,id',
            'turno'      => 'nullable|string|max:30',
        ];
    }

    public function messages()
    {
        return [
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
        ];
    }
}
