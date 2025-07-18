<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    protected $table = 'registros';

    protected $fillable = [
        'Historial_Medico_Id',
        'Fecha_Hora',
        'Antecedentes',
        'Medicacion_Actual',
        'Postura',
        'Marcha',
        'Fuerza_Muscular',
        'Rango_Movimiento_Muscular_ROM',
        'Tono_Muscular',
        'Localizacion_Dolor',
        'Intensidad_Dolor',
        'Tipo_Dolor',
        'Movilidad_Articular',
        'Balance_y_Coordinacion',
        'Sensibilidad',
        'Reflejos_Osteotendinosos',
        'Motivo_Visita',
        'Numero_Sesion',
    ];

    public function historial()
    {
        return $this->belongsTo(HistorialMedico::class, 'Historial_Medico_Id');
    }
}
