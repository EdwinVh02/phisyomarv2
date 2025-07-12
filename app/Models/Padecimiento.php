<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Padecimiento extends Model
{
    protected $table = 'padecimiento';

    protected $fillable = [
        'Nombre',
        'Sintomas',
        'Clasificacion',
        'Nivel_Gravedad',
        'Codigo_CIE10',
        'Origen',
        'Estudios_Imagen'
    ];

    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'PadecimientoId');
    }

    public function define()
    {
        return $this->belongsToMany(Tratamiento::class, 'define', 'PadecimientoId', 'TratamientoId')
            ->withPivot('AdministradorId');
    }
}
