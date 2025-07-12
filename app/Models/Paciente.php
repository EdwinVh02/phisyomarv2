<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'pacientes';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Id',
        'Contacto_Emergencia_Nombre',
        'Contacto_Emergencia_Telefono',
        'Contacto_Emergencia_Parentesco',
        'Tutor_Nombre',
        'Tutor_Telefono',
        'Tutor_Parentesco',
        'Tutor_Direccion',
        'Historial_Medico_Id'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Id');
    }

    public function historial()
    {
        return $this->belongsTo(HistorialMedico::class, 'Historial_Medico_Id');
    }

    public function consentimientos()
    {
        return $this->hasMany(ConsentimientoInformado::class, 'PacienteId');
    }
}
