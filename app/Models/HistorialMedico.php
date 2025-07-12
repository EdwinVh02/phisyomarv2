<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialMedico extends Model
{
    protected $table = 'historial_medico';

    protected $fillable = ['PacienteId', 'Fecha_Creacion', 'Observacion_General'];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'PacienteId');
    }

    public function registros()
    {
        return $this->hasMany(Registro::class, 'Historial_Medico_Id');
    }
}
