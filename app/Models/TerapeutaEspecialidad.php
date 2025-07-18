<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TerapeutaEspecialidad extends Pivot
{
    protected $table = 'terapeuta_especialidad';

    protected $fillable = [
        'TerapeutaId',
        'EspecialidadId',
    ];
}
