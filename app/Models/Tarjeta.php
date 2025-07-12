<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarjeta extends Model
{
    protected $table = 'tarjeta';

    protected $fillable = [
        'Numero',
        'Titular',
        'Banco',
        'CVV',
        'Fecha_Vencimiento',
        'PagoId'
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'PagoId');
    }
}
