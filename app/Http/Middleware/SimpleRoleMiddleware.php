<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimpleRoleMiddleware
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
        
        // Obtener el rol_id del usuario
        $userRoleId = $user->rol_id;
        
        if (!$userRoleId) {
            return response()->json(['error' => 'Usuario sin rol asignado'], 403);
        }

        // Convertir roles permitidos a array de enteros
        $allowedRoles = array_map('intval', $roles);
        
        // Verificar si el usuario tiene uno de los roles permitidos
        if (!in_array($userRoleId, $allowedRoles)) {
            return response()->json([
                'error' => 'Acceso denegado. Roles permitidos: ' . implode(', ', $allowedRoles),
                'user_role' => $userRoleId
            ], 403);
        }

        return $next($request);
    }
}