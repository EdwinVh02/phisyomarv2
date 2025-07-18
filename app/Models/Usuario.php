<?php

namespace App\Models;

use App\Helpers\RoleHelper; // Si vas a usar Auth
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'correo_electronico',
        'contraseña',
        'telefono',
        'direccion',
        'fecha_nacimiento',
        'sexo',
        'curp',
        'ocupacion',
        'estatus',
        'rol_id',
    ];

    protected $hidden = ['contraseña', 'remember_token'];

    protected $rememberTokenName = 'remember_token';

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación con Rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // Relación 1 a 1 con Paciente, Terapeuta, etc.
    public function paciente()
    {
        return $this->hasOne(Paciente::class, 'id');
    }

    public function terapeuta()
    {
        return $this->hasOne(Terapeuta::class, 'id');
    }

    public function recepcionista()
    {
        return $this->hasOne(Recepcionista::class, 'id');
    }

    public function administrador()
    {
        return $this->hasOne(Administrador::class, 'id');
    }

    // Mutador para hashear contraseña
    public function setContraseñaAttribute($value)
    {
        $this->attributes['contraseña'] = bcrypt($value);
    }

    // Métodos requeridos para autenticación
    public function getAuthPassword()
    {
        return $this->contraseña;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getEmailForPasswordReset()
    {
        return $this->correo_electronico;
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    // ===== HELPERS DE ROLES =====

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($roleId): bool
    {
        return RoleHelper::hasRole($this, $roleId);
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public function hasAnyRole(array $roleIds): bool
    {
        return RoleHelper::hasAnyRole($this, $roleIds);
    }

    /**
     * Verificar si es administrador
     */
    public function isAdmin(): bool
    {
        return RoleHelper::isAdmin($this);
    }

    /**
     * Verificar si es terapeuta
     */
    public function isTerapeuta(): bool
    {
        return RoleHelper::isTerapeuta($this);
    }

    /**
     * Verificar si es recepcionista
     */
    public function isRecepcionista(): bool
    {
        return RoleHelper::isRecepcionista($this);
    }

    /**
     * Verificar si es paciente
     */
    public function isPaciente(): bool
    {
        return RoleHelper::isPaciente($this);
    }

    /**
     * Verificar si puede gestionar pacientes
     */
    public function canManagePatients(): bool
    {
        return RoleHelper::canManagePatients($this);
    }

    /**
     * Verificar si puede ver estadísticas generales
     */
    public function canViewGeneralStats(): bool
    {
        return RoleHelper::canViewGeneralStats($this);
    }

    /**
     * Verificar si puede acceder a información financiera
     */
    public function canAccessFinancials(): bool
    {
        return RoleHelper::canAccessFinancials($this);
    }

    /**
     * Obtener nombre del rol
     */
    public function getRoleName(): ?string
    {
        return RoleHelper::getRoleName($this);
    }

    /**
     * Verificar si tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        return RoleHelper::hasPermission($this, $permission);
    }

    /**
     * Obtener dashboard URL según el rol
     */
    public function getDashboardUrl(): string
    {
        return RoleHelper::getDashboardUrl($this);
    }
}
