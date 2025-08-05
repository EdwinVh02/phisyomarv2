<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'fecha_hora',
        'monto',
        'forma_pago',
        'recibo',
        'cita_id',
        'paquete_paciente_id',
        'autorizacion',
        'factura_emitida',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'monto' => 'decimal:2',
        'factura_emitida' => 'boolean',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function paquetePaciente()
    {
        return $this->belongsTo(PaquetePaciente::class, 'paquete_paciente_id');
    }
}
