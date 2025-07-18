<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Valoracion extends Model
{
    protected $table = 'valoraciones';

    protected $fillable = [
        'Puntuacion',
        'Fecha_Hora',
        'PacienteId',
        'TerapeutaId',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class, 'terapeuta_id');
    }
}
