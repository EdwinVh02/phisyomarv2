<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Terapeuta extends Model
{
    protected $table = 'terapeuta';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Id',
        'Cedula_Profesional',
        'Especialidad_Principal',
        'Experiencia_Anios',
        'Estatus'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Id');
    }

    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'terapeuta_especialidad', 'TerapeutaId', 'EspecialidadId');
    }

    public function experiencias()
    {
        return $this->hasMany(ExperienciaTerapeuta::class, 'TerapeutaId');
    }
}
