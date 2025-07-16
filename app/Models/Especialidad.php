<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';

    protected $fillable = ['Nombre'];

    public function terapeutas()
    {
        return $this->belongsToMany(Terapeuta::class, 'terapeuta_especialidad', 'EspecialidadId', 'TerapeutaId');
    }
}
