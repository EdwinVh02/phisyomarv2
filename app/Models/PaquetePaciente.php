<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaquetePaciente extends Model
{
    protected $table = 'paquete_pacientes';
    
    public $timestamps = false;

    protected $fillable = [
        'paciente_id',
        'paquete_sesion_id',
        'fecha_compra',
        'sesiones_usadas',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function paqueteSesion()
    {
        return $this->belongsTo(PaqueteSesion::class, 'paquete_sesion_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'paquete_paciente_id');
    }
}
