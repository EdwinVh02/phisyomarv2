<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\Usuario;

class CitaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Usuario $user): bool
    {
        $roleId = $user->rol->id ?? null;

        // Administrador (1) y Recepcionista (3) pueden ver todas las citas
        return in_array($roleId, [1, 3]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Usuario $user, Cita $cita): bool
    {
        $roleId = $user->rol->id ?? null;

        // Administrador (1) y Recepcionista (3) pueden ver cualquier cita
        if (in_array($roleId, [1, 3])) {
            return true;
        }

        // Terapeuta (2) puede ver solo sus propias citas
        if ($roleId === 2) {
            return $cita->terapeuta_id === $user->terapeuta->id ?? null;
        }

        // Paciente (4) puede ver solo sus propias citas
        if ($roleId === 4) {
            return $cita->paciente_id === $user->paciente->id ?? null;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Usuario $user): bool
    {
        $roleId = $user->rol->id ?? null;

        // Administrador (1), Recepcionista (3) y Paciente (4) pueden crear citas
        return in_array($roleId, [1, 3, 4]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Usuario $user, Cita $cita): bool
    {
        $roleId = $user->rol->id ?? null;

        // Administrador (1) y Recepcionista (3) pueden modificar cualquier cita
        if (in_array($roleId, [1, 3])) {
            return true;
        }

        // Terapeuta (2) puede modificar sus propias citas (datos clínicos)
        if ($roleId === 2) {
            return $cita->terapeuta_id === $user->terapeuta->id ?? null;
        }

        // Paciente (4) puede modificar sus propias citas (solo si no han empezado)
        if ($roleId === 4) {
            return $cita->paciente_id === $user->paciente->id ?? null &&
                   $cita->estado !== 'Realizada';
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Usuario $user, Cita $cita): bool
    {
        $roleId = $user->rol->id ?? null;

        // Administrador (1) y Recepcionista (3) pueden eliminar citas
        if (in_array($roleId, [1, 3])) {
            return true;
        }

        // Paciente (4) puede cancelar sus propias citas
        if ($roleId === 4) {
            return $cita->paciente_id === $user->paciente->id ?? null &&
                   $cita->estado !== 'Realizada';
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Usuario $user, Cita $cita): bool
    {
        return $user->rol->id === 1; // Solo administrador
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Usuario $user, Cita $cita): bool
    {
        return $user->rol->id === 1; // Solo administrador
    }
}
