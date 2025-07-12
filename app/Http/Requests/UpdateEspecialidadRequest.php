<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEspecialidadRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'nombre'      => 'sometimes|string|max:100|unique:especialidads,nombre,' . $this->route('especialidad'),
            'descripcion' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'nombre.unique' => 'Ya existe una especialidad con ese nombre.',
            'nombre.max'    => 'El nombre no debe exceder 100 caracteres.',
        ];
    }
}
