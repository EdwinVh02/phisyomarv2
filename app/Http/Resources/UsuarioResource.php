<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'correo_electronico' => $this->correo_electronico,
            'contraseña' => $this->contraseña,
            'telefono' => $this->telefono,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'sexo' => $this->sexo,
            'curp' => $this->curp,
            'ocupacion' => $this->ocupacion,
            'estatus' => $this->estatus,
            'rol' => $this->whenLoaded('rol'),
            'created_at' => $this->created_at,
        ];
    }
}
