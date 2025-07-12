<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'cita';

    protected $fillable = [
        'Fecha_Hora',
        'Tipo',
        'Duracion',
        'Ubicacion',
        'Equipo_Asignado',
        'Motivo',
        'Estado',
        'PacienteId',
        'TerapeutaId',
        'RegistroId',
        'PaquetePacienteId',
        'Observaciones',
        'Escala_Dolor_EVA_Inicio',
        'Escala_Dolor_EVA_Fin',
        'Como_Fue_Lesion',
        'Antecedentes_Patologicos',
        'Antecedentes_No_Patologicos'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'PacienteId');
    }

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class, 'TerapeutaId');
    }

    public function paquetePaciente()
    {
        return $this->belongsTo(PaquetePaciente::class, 'PaquetePacienteId');
    }
}
