<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileCompleteness
{
    /**
     * Manejar una solicitud entrante.
     * 
     * Verificar si el usuario tiene su perfil específico completo según su rol.
     * Si no está completo, redirigir a la página de completar perfil.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar a usuarios autenticados
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Rutas que no requieren verificación de perfil completo
        $excludedRoutes = [
            'api/auth/logout',
            'api/user/profile/complete',
            'api/user/profile/check-completeness',
            'api/auth/user', // Para obtener datos del usuario actual
        ];

        // Si es una ruta excluida, continuar
        if ($this->shouldSkipCheck($request, $excludedRoutes)) {
            return $next($request);
        }

        // Verificar completitud del perfil según el rol
        $profileStatus = $this->checkProfileCompleteness($user);
        
        if (!$profileStatus['complete']) {
            // Si es una petición AJAX/API, devolver respuesta JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'profile_incomplete' => true,
                    'role' => $user->getRoleName(),
                    'missing_fields' => $profileStatus['missing_fields'],
                    'redirect_url' => $this->getProfileCompletionUrl($user),
                    'message' => 'Perfil incompleto. Es necesario completar la información antes de continuar.'
                ], 200); // 200 para que el frontend pueda manejar la redirección
            }

            // Para requests web normales, redireccionar
            return redirect($this->getProfileCompletionUrl($user))
                ->with('profile_incomplete', true)
                ->with('missing_fields', $profileStatus['missing_fields']);
        }

        return $next($request);
    }

    /**
     * Verificar si se debe omitir la verificación de completitud del perfil
     */
    private function shouldSkipCheck(Request $request, array $excludedRoutes): bool
    {
        $currentPath = $request->path();
        
        foreach ($excludedRoutes as $route) {
            if (str_starts_with($currentPath, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar la completitud del perfil según el rol del usuario
     */
    private function checkProfileCompleteness($user): array
    {
        $missingFields = [];
        $complete = true;

        switch ($user->rol_id) {
            case 4: // Paciente
                $paciente = $user->paciente;
                if (!$paciente) {
                    // Crear automáticamente el registro de paciente si no existe
                    try {
                        $paciente = \App\Models\Paciente::create(['id' => $user->id]);
                        $user->load('paciente'); // Recargar la relación
                    } catch (\Exception $e) {
                        $missingFields[] = 'registro_paciente';
                        $complete = false;
                    }
                }
                
                if ($paciente) {
                    // Verificar campos obligatorios para pacientes
                    if (!$paciente->contacto_emergencia_nombre) {
                        $missingFields[] = 'contacto_emergencia_nombre';
                    }
                    if (!$paciente->contacto_emergencia_telefono) {
                        $missingFields[] = 'contacto_emergencia_telefono';
                    }
                    if (!$paciente->contacto_emergencia_parentesco) {
                        $missingFields[] = 'contacto_emergencia_parentesco';
                    }
                    
                    // Si falta algún campo crítico
                    if (!empty($missingFields)) {
                        $complete = false;
                    }
                }
                break;

            case 2: // Terapeuta
                $terapeuta = $user->terapeuta;
                if (!$terapeuta) {
                    // Crear automáticamente el registro de terapeuta si no existe
                    try {
                        $terapeuta = \App\Models\Terapeuta::create(['id' => $user->id]);
                        $user->load('terapeuta'); // Recargar la relación
                    } catch (\Exception $e) {
                        $missingFields[] = 'registro_terapeuta';
                        $complete = false;
                    }
                }
                
                if ($terapeuta) {
                    // Verificar campos obligatorios para terapeutas
                    if (!$terapeuta->cedula_profesional) {
                        $missingFields[] = 'cedula_profesional';
                    }
                    if (!$terapeuta->especialidad_principal) {
                        $missingFields[] = 'especialidad_principal';
                    }
                    if (!$terapeuta->experiencia_anios) {
                        $missingFields[] = 'experiencia_anios';
                    }
                    
                    if (!empty($missingFields)) {
                        $complete = false;
                    }
                }
                break;

            case 3: // Recepcionista
                $recepcionista = $user->recepcionista;
                if (!$recepcionista) {
                    // Crear automáticamente el registro de recepcionista si no existe
                    try {
                        $recepcionista = \App\Models\Recepcionista::create(['id' => $user->id]);
                        $user->load('recepcionista'); // Recargar la relación
                    } catch (\Exception $e) {
                        $missingFields[] = 'registro_recepcionista';
                        $complete = false;
                    }
                }
                // Los recepcionistas pueden tener campos menos críticos
                // que se pueden completar opcionalmente
                break;

            case 1: // Administrador
                $administrador = $user->administrador;
                if (!$administrador) {
                    // Crear automáticamente el registro de administrador si no existe
                    try {
                        $administrador = \App\Models\Administrador::create(['id' => $user->id]);
                        $user->load('administrador'); // Recargar la relación
                    } catch (\Exception $e) {
                        $missingFields[] = 'registro_administrador';
                        $complete = false;
                    }
                }
                
                if ($administrador) {
                    // Verificar campos opcionales pero recomendados
                    if (!$administrador->cedula_profesional) {
                        $missingFields[] = 'cedula_profesional';
                    }
                    if (!$administrador->clinica_id) {
                        $missingFields[] = 'clinica_id';
                    }
                    
                    // Para administradores, solo advertir pero no bloquear
                    // $complete = !empty($missingFields) ? false : true;
                }
                break;
        }

        return [
            'complete' => $complete,
            'missing_fields' => $missingFields
        ];
    }

    /**
     * Obtener la URL para completar el perfil según el rol
     */
    private function getProfileCompletionUrl($user): string
    {
        switch ($user->rol_id) {
            case 4: // Paciente
                return '/paciente/profile/complete';
            case 2: // Terapeuta
                return '/terapeuta/profile/complete';
            case 3: // Recepcionista
                return '/recepcionista/profile/complete';
            case 1: // Administrador
                return '/admin/profile/complete';
            default:
                return '/profile/complete';
        }
    }
}