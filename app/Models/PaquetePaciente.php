<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaquetePaciente extends Model
{
    protected $table = 'paquete_pacientes';

    protected $fillable = [
        'PacienteId',
        'PaqueteSesionId',
        'Fecha_Compra',
        'Sesiones_Usadas',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'PacienteId');
    }

    public function paqueteSesion()
    {
        return $this->belongsTo(PaqueteSesion::class, 'PaqueteSesionId');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'PaquetePacienteId');
    }
}
