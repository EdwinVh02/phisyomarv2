<?php

namespace App\Observers;

use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Terapeuta;
use App\Models\Recepcionista;
use App\Models\Administrador;
use Illuminate\Support\Facades\Log;

class UsuarioObserver
{
    /**
     * Manejar el evento "created" del modelo Usuario.
     * Crear automáticamente el registro en la tabla específica del rol.
     */
    public function created(Usuario $usuario): void
    {
        try {
            $this->createRoleSpecificRecord($usuario);
            
            Log::info("Usuario creado con registro específico de rol", [
                'usuario_id' => $usuario->id,
                'rol_id' => $usuario->rol_id,
                'email' => $usuario->correo_electronico
            ]);
        } catch (\Exception $e) {
            Log::error("Error al crear registro específico de rol", [
                'usuario_id' => $usuario->id,
                'rol_id' => $usuario->rol_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Manejar el evento "updated" del modelo Usuario.
     * Si cambió el rol, gestionar los registros correspondientes.
     */
    public function updated(Usuario $usuario): void
    {
        // Verificar si cambió el rol_id
        if ($usuario->isDirty('rol_id')) {
            try {
                $oldRolId = $usuario->getOriginal('rol_id');
                $newRolId = $usuario->rol_id;
                
                Log::info("Cambio de rol detectado", [
                    'usuario_id' => $usuario->id,
                    'rol_anterior' => $oldRolId,
                    'rol_nuevo' => $newRolId
                ]);
                
                // Remover registro del rol anterior si existe
                $this->removeOldRoleRecord($usuario, $oldRolId);
                
                // Crear registro para el nuevo rol
                $this->createRoleSpecificRecord($usuario);
                
            } catch (\Exception $e) {
                Log::error("Error al gestionar cambio de rol", [
                    'usuario_id' => $usuario->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Crear el registro específico según el rol del usuario
     */
    private function createRoleSpecificRecord(Usuario $usuario): void
    {
        switch ($usuario->rol_id) {
            case 4: // Paciente
                if (!Paciente::find($usuario->id)) {
                    Paciente::create([
                        'id' => $usuario->id,
                        // Los demás campos se llenarán después por el usuario/recepcionista
                    ]);
                    Log::info("Registro de paciente creado", ['usuario_id' => $usuario->id]);
                }
                break;

            case 2: // Terapeuta
                if (!Terapeuta::find($usuario->id)) {
                    Terapeuta::create([
                        'id' => $usuario->id,
                        'estatus' => 'activo',
                        // cedula_profesional, especialidad_principal, etc. se llenarán después
                    ]);
                    Log::info("Registro de terapeuta creado", ['usuario_id' => $usuario->id]);
                }
                break;

            case 3: // Recepcionista
                if (!Recepcionista::find($usuario->id)) {
                    Recepcionista::create([
                        'id' => $usuario->id,
                    ]);
                    Log::info("Registro de recepcionista creado", ['usuario_id' => $usuario->id]);
                }
                break;

            case 1: // Administrador
                if (!Administrador::find($usuario->id)) {
                    Administrador::create([
                        'id' => $usuario->id,
                        // cedula_profesional y clinica_id se llenarán después
                    ]);
                    Log::info("Registro de administrador creado", ['usuario_id' => $usuario->id]);
                }
                break;

            default:
                Log::warning("Rol no reconocido para crear registro específico", [
                    'usuario_id' => $usuario->id,
                    'rol_id' => $usuario->rol_id
                ]);
                break;
        }
    }

    /**
     * Remover el registro del rol anterior cuando cambia de rol
     */
    private function removeOldRoleRecord(Usuario $usuario, int $oldRolId): void
    {
        try {
            switch ($oldRolId) {
                case 4: // Era Paciente
                    $paciente = Paciente::find($usuario->id);
                    if ($paciente) {
                        // Verificar si tiene citas o historial antes de eliminar
                        if ($paciente->citas()->count() > 0 || $paciente->historialMedico) {
                            Log::warning("No se puede eliminar paciente con citas o historial", [
                                'usuario_id' => $usuario->id
                            ]);
                            // Mantener el registro pero marcarlo como inactivo o algo similar
                            // Opcional: agregar un campo 'activo' a las tablas específicas
                        } else {
                            $paciente->delete();
                            Log::info("Registro de paciente eliminado", ['usuario_id' => $usuario->id]);
                        }
                    }
                    break;

                case 2: // Era Terapeuta
                    $terapeuta = Terapeuta::find($usuario->id);
                    if ($terapeuta) {
                        // Verificar si tiene citas asignadas
                        if ($terapeuta->valoraciones()->count() > 0) {
                            Log::warning("No se puede eliminar terapeuta con valoraciones", [
                                'usuario_id' => $usuario->id
                            ]);
                            // Marcar como inactivo en lugar de eliminar
                            $terapeuta->update(['estatus' => 'inactivo']);
                        } else {
                            $terapeuta->delete();
                            Log::info("Registro de terapeuta eliminado", ['usuario_id' => $usuario->id]);
                        }
                    }
                    break;

                case 3: // Era Recepcionista
                    $recepcionista = Recepcionista::find($usuario->id);
                    if ($recepcionista) {
                        $recepcionista->delete();
                        Log::info("Registro de recepcionista eliminado", ['usuario_id' => $usuario->id]);
                    }
                    break;

                case 1: // Era Administrador
                    $administrador = Administrador::find($usuario->id);
                    if ($administrador) {
                        $administrador->delete();
                        Log::info("Registro de administrador eliminado", ['usuario_id' => $usuario->id]);
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::error("Error al remover registro de rol anterior", [
                'usuario_id' => $usuario->id,
                'rol_anterior' => $oldRolId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Manejar el evento "deleting" del modelo Usuario.
     * Limpiar registros relacionados antes de eliminar el usuario.
     */
    public function deleting(Usuario $usuario): void
    {
        try {
            // Los registros específicos se eliminarán automáticamente por CASCADE
            // pero podemos hacer limpieza adicional si es necesario
            
            Log::info("Usuario eliminado", [
                'usuario_id' => $usuario->id,
                'email' => $usuario->correo_electronico
            ]);
        } catch (\Exception $e) {
            Log::error("Error al eliminar usuario", [
                'usuario_id' => $usuario->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}