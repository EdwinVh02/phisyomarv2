<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre' => 'sometimes|string|max:100',
            'apellido_paterno' => 'sometimes|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'correo_electronico' => 'sometimes|email|unique:usuarios,email,'.$this->usuario,
            'telefono' => 'nullable|string|max:20',
            'contrasena' => 'nullable|string|min:6',
            'rol_id' => 'sometimes|exists:rols,id',
            'estatus' => 'nullable|in:activo,inactivo',
        ];
    }

    public function messages()
    {
        return [
            'correo_electronico.unique' => 'El correo electrónico ya está en uso.',
            'rol_id.exists' => 'El rol seleccionado no existe.',
        ];
    }
}
