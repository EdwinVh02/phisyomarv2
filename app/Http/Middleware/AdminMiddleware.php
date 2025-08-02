<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Middleware específico para administradores
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (!$request->user()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $user = $request->user();

        // Verificar que tenga rol de administrador
        if (!$user->isAdmin()) {
            return response()->json([
                'error' => 'Acceso denegado. Se requieren permisos de administrador.',
                'user_role' => $user->getRoleName(),
                'required_role' => 'Administrador'
            ], 403);
        }

        // Verificar que el usuario esté activo
        if ($user->estatus !== 'activo') {
            return response()->json([
                'error' => 'Usuario inactivo o suspendido',
                'status' => $user->estatus
            ], 403);
        }

        return $next($request);
    }
}