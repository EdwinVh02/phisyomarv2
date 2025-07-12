<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaqueteSesion extends Model
{
    protected $table = 'paquetesesion';

    protected $fillable = [
        'Nombre',
        'Numero_Sesiones',
        'Precio',
        'Tipo_Terapia',
        'Especifico_Enfermedad'
    ];

    public function paquetesPaciente()
    {
        return $this->hasMany(PaquetePaciente::class, 'PaqueteSesionId');
    }
}
