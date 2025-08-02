<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Terapeuta;
use App\Models\Recepcionista;
use App\Models\Administrador;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileCompletionService
{
    /**
     * Completar el perfil de un usuario según su rol
     */
    public function completeProfile(Usuario $user, array $data): array
    {
        try {
            DB::beginTransaction();

            $result = match ($user->rol_id) {
                4 => $this->completePacienteProfile($user, $data),
                2 => $this->completeTerapeutaProfile($user, $data),
                3 => $this->completeRecepcionistaProfile($user, $data),
                1 => $this->completeAdministradorProfile($user, $data),
                default => throw new \InvalidArgumentException('Rol no válido')
            };

            DB::commit();

            return [
                'success' => true,
                'message' => 'Perfil completado exitosamente',
                'data' => $result
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al completar perfil', [
                'usuario_id' => $user->id,
                'rol_id' => $user->rol_id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error al completar perfil: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Completar perfil de paciente
     */
    private function completePacienteProfile(Usuario $user, array $data): Paciente
    {
        $paciente = $user->paciente ?? Paciente::create(['id' => $user->id]);

        $pacienteData = [
            'contacto_emergencia_nombre' => $data['contacto_emergencia_nombre'] ?? null,
            'contacto_emergencia_telefono' => $data['contacto_emergencia_telefono'] ?? null,
            'contacto_emergencia_parentesco' => $data['contacto_emergencia_parentesco'] ?? null,
            'tutor_nombre' => $data['tutor_nombre'] ?? null,
            'tutor_telefono' => $data['tutor_telefono'] ?? null,
            'tutor_parentesco' => $data['tutor_parentesco'] ?? null,
            'tutor_direccion' => $data['tutor_direccion'] ?? null,
        ];

        $paciente->update(array_filter($pacienteData));

        Log::info('Perfil de paciente completado', [
            'usuario_id' => $user->id,
            'paciente_id' => $paciente->id
        ]);

        return $paciente->fresh();
    }

    /**
     * Completar perfil de terapeuta
     */
    private function completeTerapeutaProfile(Usuario $user, array $data): Terapeuta
    {
        $terapeuta = $user->terapeuta ?? Terapeuta::create([
            'id' => $user->id,
            'estatus' => 'activo'
        ]);

        $terapeutaData = [
            'cedula_profesional' => $data['cedula_profesional'] ?? null,
            'especialidad_principal' => $data['especialidad_principal'] ?? null,
            'experiencia_anios' => $data['experiencia_anios'] ?? null,
            'estatus' => $data['estatus'] ?? 'activo',
        ];

        $terapeuta->update(array_filter($terapeutaData));

        // Si se especificaron especialidades adicionales, manejarlas
        if (isset($data['especialidades']) && is_array($data['especialidades'])) {
            $terapeuta->especialidades()->sync($data['especialidades']);
        }

        Log::info('Perfil de terapeuta completado', [
            'usuario_id' => $user->id,
            'terapeuta_id' => $terapeuta->id
        ]);

        return $terapeuta->fresh();
    }

    /**
     * Completar perfil de recepcionista
     */
    private function completeRecepcionistaProfile(Usuario $user, array $data): Recepcionista
    {
        $recepcionista = $user->recepcionista ?? Recepcionista::create(['id' => $user->id]);

        // Los recepcionistas pueden tener campos específicos como:
        // extension_telefonica, horario_asignado, etc.
        $recepcionistaData = [
            // Agregar campos específicos cuando se definan en la BD
        ];

        if (!empty($recepcionistaData)) {
            $recepcionista->update(array_filter($recepcionistaData));
        }

        Log::info('Perfil de recepcionista completado', [
            'usuario_id' => $user->id,
            'recepcionista_id' => $recepcionista->id
        ]);

        return $recepcionista->fresh();
    }

    /**
     * Completar perfil de administrador
     */
    private function completeAdministradorProfile(Usuario $user, array $data): Administrador
    {
        $administrador = $user->administrador ?? Administrador::create(['id' => $user->id]);

        $administradorData = [
            'cedula_profesional' => $data['cedula_profesional'] ?? null,
            'clinica_id' => $data['clinica_id'] ?? null,
        ];

        $administrador->update(array_filter($administradorData));

        Log::info('Perfil de administrador completado', [
            'usuario_id' => $user->id,
            'administrador_id' => $administrador->id
        ]);

        return $administrador->fresh();
    }

    /**
     * Verificar si un perfil está completo
     */
    public function isProfileComplete(Usuario $user): array
    {
        $missingFields = [];
        $complete = true;

        switch ($user->rol_id) {
            case 4: // Paciente
                $paciente = $user->paciente;
                if (!$paciente) {
                    return ['complete' => false, 'missing_fields' => ['registro_paciente']];
                }

                $requiredFields = [
                    'contacto_emergencia_nombre',
                    'contacto_emergencia_telefono',
                    'contacto_emergencia_parentesco'
                ];

                foreach ($requiredFields as $field) {
                    if (empty($paciente->$field)) {
                        $missingFields[] = $field;
                    }
                }
                break;

            case 2: // Terapeuta
                $terapeuta = $user->terapeuta;
                if (!$terapeuta) {
                    return ['complete' => false, 'missing_fields' => ['registro_terapeuta']];
                }

                $requiredFields = [
                    'cedula_profesional',
                    'especialidad_principal',
                    'experiencia_anios'
                ];

                foreach ($requiredFields as $field) {
                    if (empty($terapeuta->$field)) {
                        $missingFields[] = $field;
                    }
                }
                break;

            case 3: // Recepcionista
                $recepcionista = $user->recepcionista;
                if (!$recepcionista) {
                    return ['complete' => false, 'missing_fields' => ['registro_recepcionista']];
                }
                // Los recepcionistas no tienen campos obligatorios por ahora
                break;

            case 1: // Administrador
                $administrador = $user->administrador;
                if (!$administrador) {
                    return ['complete' => false, 'missing_fields' => ['registro_administrador']];
                }
                // Los administradores pueden funcionar sin campos específicos obligatorios
                break;
        }

        return [
            'complete' => empty($missingFields),
            'missing_fields' => $missingFields
        ];
    }

    /**
     * Obtener los campos requeridos para un rol específico
     */
    public function getRequiredFieldsForRole(int $rolId): array
    {
        return match ($rolId) {
            4 => [ // Paciente
                'contacto_emergencia_nombre' => 'Nombre del contacto de emergencia',
                'contacto_emergencia_telefono' => 'Teléfono del contacto de emergencia',
                'contacto_emergencia_parentesco' => 'Parentesco del contacto de emergencia'
            ],
            2 => [ // Terapeuta
                'cedula_profesional' => 'Cédula profesional',
                'especialidad_principal' => 'Especialidad principal',
                'experiencia_anios' => 'Años de experiencia'
            ],
            3 => [ // Recepcionista
                // Campos opcionales por ahora
            ],
            1 => [ // Administrador
                'cedula_profesional' => 'Cédula profesional (opcional)',
                'clinica_id' => 'Clínica asignada (opcional)'
            ],
            default => []
        };
    }
}