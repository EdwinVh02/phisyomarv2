<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrador extends Model
{
    protected $table = 'administradors';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['id', 'cedula_profesional', 'clinicaid'];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id');
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class, 'clinicaid');
    }
}
