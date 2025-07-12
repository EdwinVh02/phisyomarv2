<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable; // Si vas a usar Auth
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'correo_electronico',
        'contraseña',
        'telefono',
        'fecha_nacimiento',
        'sexo',
        'curp',
        'estatus',
        'rol_id'
    ];

    protected $hidden = ['contraseña', 'remember_token'];

    protected $casts = [
        'Fecha_Nacimiento' => 'date',
        'Fecha_Creacion' => 'datetime',
        'Fecha_Actualizacion' => 'datetime',
    ];

    // Relación con Rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'RolId');
    }

    // Relación 1 a 1 con Paciente, Terapeuta, etc.
    public function paciente()
    {
        return $this->hasOne(Paciente::class, 'Id');
    }
    public function terapeuta()
    {
        return $this->hasOne(Terapeuta::class, 'Id');
    }
    public function recepcionista()
    {
        return $this->hasOne(Recepcionista::class, 'Id');
    }
    public function administrador()
    {
        return $this->hasOne(Administrador::class, 'Id');
    }

    // Mutador para hashear contraseña
    public function setContraseñaAttribute($value)
    {
        $this->attributes['contraseña'] = bcrypt($value);
    }
}
