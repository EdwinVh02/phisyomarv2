<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    protected $table = 'respuestas';

    protected $fillable = [
        'Texto',
        'Tipo',
        'PreguntaId',
        'PacienteId',
        'CitaId',
        'TratamientoId',
        'Fecha_Respuesta',
    ];

    public function pregunta()
    {
        return $this->belongsTo(Pregunta::class, 'PreguntaId');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'PacienteId');
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'CitaId');
    }

    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class, 'TratamientoId');
    }
}
