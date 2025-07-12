<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Valoracion extends Model
{
    protected $table = 'valoracion';

    protected $fillable = [
        'Puntuacion',
        'Fecha_Hora',
        'PacienteId',
        'TerapeutaId'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'PacienteId');
    }

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class, 'TerapeutaId');
    }
}
