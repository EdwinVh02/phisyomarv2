<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adjunto extends Model
{
    protected $table = 'adjuntos';

    protected $fillable = [
        'Entidad',
        'EntidadId',
        'Nombre_Archivo',
        'URL',
        'Fecha_Subida'
    ];
}
