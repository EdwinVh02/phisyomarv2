<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarifa extends Model
{
    protected $table = 'tarifas';

    protected $fillable = [
        'Titulo',
        'Precio',
        'Tipo',
        'Condiciones',
    ];

    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'TarifaId');
    }
}
