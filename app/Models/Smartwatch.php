<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Smartwatch extends Model
{
    protected $table = 'smartwatch';

    protected $fillable = [
        'ValoracionId',
        'PacienteId',
        'Datos'
    ];

    public function valoracion()
    {
        return $this->belongsTo(Valoracion::class, 'ValoracionId');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'PacienteId');
    }
}
