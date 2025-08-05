<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaqueteSesion extends Model
{
    protected $table = 'paquete_sesions';
    
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'numero_sesiones',
        'precio',
        'tipo_terapia',
        'especifico_enfermedad',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function paquetesPaciente()
    {
        return $this->hasMany(PaquetePaciente::class, 'paquete_sesion_id');
    }
}
