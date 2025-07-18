<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{
    protected $table = 'tratamientos';

    protected $fillable = [
        'Titulo',
        'Descripcion',
        'Duracion',
        'Frecuencia',
        'Requisitos',
        'PadecimientoId',
        'TarifaId',
    ];

    public function padecimiento()
    {
        return $this->belongsTo(Padecimiento::class, 'PadecimientoId');
    }

    public function tarifa()
    {
        return $this->belongsTo(Tarifa::class, 'TarifaId');
    }

    public function define()
    {
        return $this->belongsToMany(Padecimiento::class, 'define', 'TratamientoId', 'PadecimientoId')
            ->withPivot('AdministradorId');
    }
}
