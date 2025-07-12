<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdministradorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'usuario_id' => 'required|exists:usuarios,id',
            'area'       => 'nullable|string|max:100',
        ];
    }
}