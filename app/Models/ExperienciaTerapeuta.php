<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExperienciaTerapeuta extends Model
{
    protected $table = 'experiencia_terapeuta';

    protected $fillable = ['TerapeutaId', 'Tipo', 'Descripcion', 'Fecha'];

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class, 'TerapeutaId');
    }
}
