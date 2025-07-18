<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pregunta extends Model
{
    protected $table = 'preguntas';

    protected $fillable = [
        'Texto',
        'EncuestaId',
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'EncuestaId');
    }

    public function respuestas()
    {
        return $this->hasMany(Respuesta::class, 'PreguntaId');
    }
}
