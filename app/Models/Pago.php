<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'Fecha_Hora',
        'Monto',
        'Forma_Pago',
        'Recibo',
        'CitaId',
        'PaquetePacienteId',
        'Autorizacion',
        'Factura_Emitida',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'CitaId');
    }

    public function paquetePaciente()
    {
        return $this->belongsTo(PaquetePaciente::class, 'PaquetePacienteId');
    }
}
