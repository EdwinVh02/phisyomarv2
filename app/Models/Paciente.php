<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'contacto_emergencia_parentesco',
        'tutor_nombre',
        'tutor_telefono',
        'tutor_parentesco',
        'tutor_direccion',
    ];


    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id');
    }

    public function historial()
    {
        return $this->hasOne(HistorialMedico::class, 'paciente_id');
    }

    public function consentimientos()
    {
        return $this->hasMany(ConsentimientoInformado::class, 'paciente_id');
    }

    public function valoraciones()
    {
        return $this->hasMany(Valoracion::class, 'paciente_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'paciente_id');
    }

    public function historialMedico()
    {
        return $this->hasOne(HistorialMedico::class, 'paciente_id');
    }

    public function paquetes()
    {
        return $this->hasMany(PaquetePaciente::class, 'paciente_id');
    }

    public function toArray()
    {
        $array = parent::toArray();
        
        // Agregar historial_medico como alias de historialMedico si existe
        if (isset($array['historial_medico']) || $this->relationLoaded('historialMedico')) {
            $array['historial_medico'] = $this->historialMedico;
        }
        
        return $array;
    }
}
