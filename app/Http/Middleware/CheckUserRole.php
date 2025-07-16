<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     * Alternativa más simple para verificar roles
     */
    public function handle(Request $request, Closure $next, $allowedRoles = null): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = $request->user();
        $userRoleId = $user->rol_id;

        // Si no se especifican roles, solo verificar autenticación
        if (!$allowedRoles) {
            return $next($request);
        }

        // Convertir roles permitidos a array
        $rolesArray = explode(',', $allowedRoles);
        $rolesArray = array_map('trim', $rolesArray);

        // Verificar si el usuario tiene un rol permitido
        if (!in_array($userRoleId, $rolesArray)) {
            return response()->json([
                'message' => 'Acceso denegado.',
                'required_roles' => $rolesArray,
                'user_role' => $userRoleId
            ], 403);
        }

        return $next($request);
    }
}
