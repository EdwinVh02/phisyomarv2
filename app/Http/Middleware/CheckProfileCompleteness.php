<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\UserRoleRegistrationService;
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

        // Crear automáticamente el registro específico si no existe
        UserRoleRegistrationService::createRoleSpecificRecord($user);
        
        // Verificar completitud del perfil según el rol
        $isComplete = UserRoleRegistrationService::isProfileComplete($user);
        $missingFields = UserRoleRegistrationService::getMissingProfileFields($user);
        
        if (!$isComplete) {
            // Si es una petición AJAX/API, devolver respuesta JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'profile_incomplete' => true,
                    'role' => $user->getRoleName(),
                    'missing_fields' => $missingFields,
                    'redirect_url' => $this->getProfileCompletionUrl($user),
                    'message' => 'Perfil incompleto. Es necesario completar la información antes de continuar.'
                ], 200); // 200 para que el frontend pueda manejar la redirección
            }

            // Para requests web normales, redireccionar
            return redirect($this->getProfileCompletionUrl($user))
                ->with('profile_incomplete', true)
                ->with('missing_fields', $missingFields);
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