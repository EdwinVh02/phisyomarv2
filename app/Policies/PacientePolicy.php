<?php

namespace App\Policies;

use App\Models\Paciente;
use App\Models\Usuario;
use Illuminate\Auth\Access\Response;

class PacientePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Usuario $user): bool
    {
        $roleId = $user->rol->id ?? null;
        
        // Administrador (1) y Recepcionista (3) pueden ver todos los pacientes
        return in_array($roleId, [1, 3]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Usuario $user, Paciente $paciente): bool
    {
        $roleId = $user->rol->id ?? null;
        
        // Administrador (1) y Recepcionista (3) pueden ver cualquier paciente
        if (in_array($roleId, [1, 3])) {
            return true;
        }
        
        // Terapeuta (2) puede ver pacientes asignados a él (através de citas)
        if ($roleId === 2) {
            return $paciente->citas()->whereHas('terapeuta', function($query) use ($user) {
                $query->where('id', $user->id);
            })->exists();
        }
        
        // Paciente (4) solo puede ver su propia información
        if ($roleId === 4) {
            return $paciente->id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Usuario $user): bool
    {
        $roleId = $user->rol->id ?? null;
        
        // Administrador (1) y Recepcionista (3) pueden crear pacientes
        return in_array($roleId, [1, 3]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Usuario $user, Paciente $paciente): bool
    {
        $roleId = $user->rol->id ?? null;
        
        // Administrador (1) y Recepcionista (3) pueden modificar cualquier paciente
        if (in_array($roleId, [1, 3])) {
            return true;
        }
        
        // Paciente (4) solo puede modificar su propia información
        if ($roleId === 4) {
            return $paciente->id === $user->id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $user, Paciente $paciente): bool
    {
        $roleId = $user->rol->id ?? null;
        
        // Solo Administrador (1) puede eliminar pacientes
        return $roleId === 1;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Usuario $user, Paciente $paciente): bool
    {
        return $user->rol->id === 1; // Solo administrador
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Usuario $user, Paciente $paciente): bool
    {
        return $user->rol->id === 1; // Solo administrador
    }
}
