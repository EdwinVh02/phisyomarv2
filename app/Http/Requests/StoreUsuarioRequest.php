<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'nombre'            => 'required|string|max:100',
            'apellido_paterno'  => 'required|string|max:100',
            'apellido_materno'  => 'nullable|string|max:100',
            'correo_electronico'             => 'required|email|unique:usuarios,email',
            'telefono'          => 'nullable|string|max:20',
            'contraseña'          => 'required|string|min:6',
            'rol_id'            => 'required|exists:rols,id',
            'estatus'           => 'nullable|in:activo,inactivo',
        ];
    }
}
