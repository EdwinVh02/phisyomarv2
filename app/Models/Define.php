<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Define extends Pivot
{
    protected $table = 'define';

    protected $fillable = [
        'PadecimientoId',
        'TratamientoId',
        'AdministradorId'
    ];
}
