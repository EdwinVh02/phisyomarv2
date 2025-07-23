<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialMedico extends Model
{
    protected $table = 'historial_medicos';

    protected $fillable = [
        'paciente_id', 
        'fecha_creacion', 
        'observacion_general',
        'motivo_consulta',
        'alergias',
        'medicamentos_actuales',
        'antecedentes_familiares',
        'cirugias_previas',
        'lesiones_previas',
        'inspeccion_general',
        'rango_movimiento',
        'fuerza_muscular',
        'pruebas_especiales',
        'dolor_eva',
        'diagnostico_fisioterapeutico',
        'frecuencia_sesiones',
        'tecnicas_propuestas',
        'objetivos_corto_plazo',
        'objetivos_mediano_plazo',
        'objetivos_largo_plazo',
        'evolucion_notas_seguimiento',
        'firma_fisioterapeuta',
        'firma_paciente'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function registros()
    {
        return $this->hasMany(Registro::class, 'Historial_Medico_Id');
    }
}
