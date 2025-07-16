<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|array $roles Roles permitidos (separados por coma)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar que el usuario esté autenticado
        if (!$request->user()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $user = $request->user();
        
        // Obtener el rol del usuario
        $userRole = $user->rol;
        
        if (!$userRole) {
            return response()->json(['error' => 'Usuario sin rol asignado'], 403);
        }

        // Convertir roles permitidos a array
        $allowedRoles = $roles;
        
        // Verificar si el usuario tiene uno de los roles permitidos
        $hasPermission = in_array($userRole->id, array_map('intval', $allowedRoles)) || 
                        in_array($userRole->name, $allowedRoles) ||
                        in_array(strtolower($userRole->name), array_map('strtolower', $allowedRoles));
        
        // Administrador siempre tiene acceso (rol ID: 1)
        if ($userRole->id === 1) {
            $hasPermission = true;
        }

        if (!$hasPermission) {
            return response()->json([
                'error' => 'Acceso denegado. Rol requerido: ' . implode(', ', $allowedRoles),
                'user_role' => $userRole->name
            ], 403);
        }

        return $next($request);
    }
}
