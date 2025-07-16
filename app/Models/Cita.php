<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';

    protected $fillable = [
        'fecha_hora',
        'tipo',
        'duracion',
        'ubicacion',
        'equipo_asignado',
        'motivo',
        'estado',
        'paciente_id',
        'terapeuta_id',
        'registro_id',
        'paquete_paciente_id',
        'observaciones',
        'escala_dolor_eva_inicio',
        'escala_dolor_eva_fin',
        'como_fue_lesion',
        'antecedentes_patologicos',
        'antecedentes_no_patologicos'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class, 'terapeuta_id');
    }

    public function paquetePaciente()
    {
        return $this->belongsTo(PaquetePaciente::class, 'paquete_paciente_id');
    }

    public function registro()
    {
        return $this->belongsTo(Registro::class, 'registro_id');
    }
}
