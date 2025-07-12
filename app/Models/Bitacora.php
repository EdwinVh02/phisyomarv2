<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacoras';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'accion',
        'tabla',
        'registro_id',
        'detalle'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Si quieres dejar acceso raw al registro modificado:
    public function registroId()
    {
        return $this->registro_id;
    }
}
