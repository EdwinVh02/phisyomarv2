<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBitacoraRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'usuario_id' => 'required|exists:usuarios,id',
            'accion' => 'required|string|max:255',
            'tabla' => 'required|string|max:50',
        ];
    }
}
