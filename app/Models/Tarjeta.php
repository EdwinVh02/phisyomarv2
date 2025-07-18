<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarjeta extends Model
{
    protected $table = 'tarjetas';

    protected $fillable = [
        'Titular',
        'Banco',
        'Fecha_Vencimiento',
        'PagoId',
    ];

    protected $hidden = [
        'Numero',
        'CVV',
    ];

    protected $casts = [
        'Fecha_Vencimiento' => 'date',
    ];

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'PagoId');
    }
}
