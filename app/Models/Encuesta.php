<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    protected $table = 'encuestas';

    protected $fillable = [
        'Titulo',
        'RecepcionistaId',
        'Tipo',
    ];

    public function preguntas()
    {
        return $this->hasMany(Pregunta::class, 'EncuestaId');
    }

    public function recepcionista()
    {
        return $this->belongsTo(Recepcionista::class, 'RecepcionistaId');
    }
}
