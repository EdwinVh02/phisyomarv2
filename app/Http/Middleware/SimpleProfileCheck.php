<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\UserRoleRegistrationService;
use Symfony\Component\HttpFoundation\Response;

class SimpleProfileCheck
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar a usuarios autenticados
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Rutas que no requieren verificación de perfil completo
        $excludedPaths = [
            'api/auth/logout',
            'api/user/profile/complete',
            'api/user/profile/check-completeness',
            'api/auth/user',
        ];

        // Si es una ruta excluida, continuar
        $currentPath = $request->path();
        foreach ($excludedPaths as $path) {
            if (str_starts_with($currentPath, $path)) {
                return $next($request);
            }
        }

        // Crear automáticamente el registro específico si no existe
        UserRoleRegistrationService::createRoleSpecificRecord($user);
        
        // Verificar completitud del perfil según el rol
        $isComplete = UserRoleRegistrationService::isProfileComplete($user);
        
        if (!$isComplete) {
            // Si es una petición AJAX/API, devolver respuesta JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                $missingFields = UserRoleRegistrationService::getMissingProfileFields($user);
                return response()->json([
                    'profile_incomplete' => true,
                    'role' => $user->getRoleName(),
                    'missing_fields' => $missingFields,
                    'redirect_url' => $this->getProfileCompletionUrl($user),
                    'message' => 'Perfil incompleto. Es necesario completar la información antes de continuar.'
                ], 200);
            }
        }

        return $next($request);
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
