<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentimientoInformado extends Model
{
    protected $table = 'consentimiento_informados';

    protected $fillable = [
        'PacienteId',
        'Fecha_Firma',
        'Tipo',
        'Documento_URL',
        'Firmado_Por',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'PacienteId');
    }
}
