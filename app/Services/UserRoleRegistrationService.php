<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Terapeuta;
use App\Models\Recepcionista;
use App\Models\Administrador;
use Illuminate\Support\Facades\Log;

class UserRoleRegistrationService
{
    /**
     * Crear automáticamente el registro específico según el rol del usuario
     * 
     * @param Usuario $usuario
     * @return bool
     */
    public static function createRoleSpecificRecord(Usuario $usuario): bool
    {
        try {
            switch ($usuario->rol_id) {
                case 4: // Paciente
                    if (!$usuario->paciente) {
                        Paciente::create(['id' => $usuario->id]);
                        Log::info("Registro de paciente creado automáticamente para usuario ID: {$usuario->id}");
                    }
                    break;
                    
                case 2: // Terapeuta
                    if (!$usuario->terapeuta) {
                        Terapeuta::create(['id' => $usuario->id]);
                        Log::info("Registro de terapeuta creado automáticamente para usuario ID: {$usuario->id}");
                    }
                    break;
                    
                case 3: // Recepcionista
                    if (!$usuario->recepcionista) {
                        Recepcionista::create(['id' => $usuario->id]);
                        Log::info("Registro de recepcionista creado automáticamente para usuario ID: {$usuario->id}");
                    }
                    break;
                    
                case 1: // Administrador
                    if (!$usuario->administrador) {
                        Administrador::create(['id' => $usuario->id]);
                        Log::info("Registro de administrador creado automáticamente para usuario ID: {$usuario->id}");
                    }
                    break;
                    
                default:
                    Log::warning("Rol desconocido para usuario ID: {$usuario->id}, rol_id: {$usuario->rol_id}");
                    return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Error al crear registro específico para usuario ID: {$usuario->id}, Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener los campos faltantes para completar el perfil según el rol
     * 
     * @param Usuario $usuario
     * @return array
     */
    public static function getMissingProfileFields(Usuario $usuario): array
    {
        $missingFields = [];
        
        // Verificar campos básicos del usuario
        if (!$usuario->fecha_nacimiento) $missingFields[] = 'fecha_nacimiento';
        if (!$usuario->sexo) $missingFields[] = 'sexo';
        if (!$usuario->curp) $missingFields[] = 'curp';
        if (!$usuario->telefono) $missingFields[] = 'telefono';
        
        // Verificar campos específicos según el rol
        switch ($usuario->rol_id) {
            case 4: // Paciente
                $paciente = $usuario->paciente;
                if ($paciente) {
                    if (!$paciente->contacto_emergencia_nombre) {
                        $missingFields[] = 'contacto_emergencia_nombre';
                    }
                    if (!$paciente->contacto_emergencia_telefono) {
                        $missingFields[] = 'contacto_emergencia_telefono';
                    }
                    if (!$paciente->contacto_emergencia_parentesco) {
                        $missingFields[] = 'contacto_emergencia_parentesco';
                    }
                }
                break;
                
            case 2: // Terapeuta
                $terapeuta = $usuario->terapeuta;
                if ($terapeuta) {
                    if (!$terapeuta->cedula_profesional) {
                        $missingFields[] = 'cedula_profesional';
                    }
                    if (!$terapeuta->especialidad_principal) {
                        $missingFields[] = 'especialidad_principal';
                    }
                    if (!$terapeuta->experiencia_anios) {
                        $missingFields[] = 'experiencia_anios';
                    }
                }
                break;
                
            case 1: // Administrador
                $administrador = $usuario->administrador;
                if ($administrador) {
                    if (!$administrador->cedula_profesional) {
                        $missingFields[] = 'cedula_profesional';
                    }
                    // clinica_id es opcional para administradores
                }
                break;
                
            case 3: // Recepcionista
                // Los recepcionistas no tienen campos específicos obligatorios
                break;
        }
        
        return $missingFields;
    }
    
    /**
     * Verificar si el perfil del usuario está completo
     * 
     * @param Usuario $usuario
     * @return bool
     */
    public static function isProfileComplete(Usuario $usuario): bool
    {
        $missingFields = self::getMissingProfileFields($usuario);
        return empty($missingFields);
    }
    
    /**
     * Obtener la información del perfil del usuario con datos específicos del rol
     * 
     * @param Usuario $usuario
     * @return array
     */
    public static function getUserProfileData(Usuario $usuario): array
    {
        $usuario->load(['rol']);
        
        $profileData = [
            'user' => $usuario,
            'role_name' => $usuario->getRoleName(),
            'profile_complete' => self::isProfileComplete($usuario),
            'missing_fields' => self::getMissingProfileFields($usuario),
        ];
        
        // Cargar datos específicos según el rol
        switch ($usuario->rol_id) {
            case 4: // Paciente
                $usuario->load('paciente');
                $profileData['paciente'] = $usuario->paciente;
                break;
                
            case 2: // Terapeuta
                $usuario->load(['terapeuta', 'terapeuta.especialidades']);
                $profileData['terapeuta'] = $usuario->terapeuta;
                break;
                
            case 3: // Recepcionista
                $usuario->load('recepcionista');
                $profileData['recepcionista'] = $usuario->recepcionista;
                break;
                
            case 1: // Administrador
                $usuario->load(['administrador', 'administrador.clinica']);
                $profileData['administrador'] = $usuario->administrador;
                break;
        }
        
        return $profileData;
    }
}