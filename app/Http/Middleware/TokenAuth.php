<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenAuth
{
    /**
     * Handle an incoming request.
     * Middleware personalizado que maneja autenticación por token
     */
    public function handle(Request $request, Closure $next, $allowedRoles = null): Response
    {
        // Obtener token del header Authorization
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        $token = substr($authHeader, 7); // Remover "Bearer "

        // Buscar el token en la tabla personal_access_tokens
        $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        
        if (!$accessToken) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        // Obtener el usuario asociado al token
        $user = $accessToken->tokenable;
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 401);
        }

        // Verificar roles si se especifican
        if ($allowedRoles) {
            $rolesArray = explode(',', $allowedRoles);
            $rolesArray = array_map('trim', $rolesArray);
            
            if (!in_array($user->rol_id, $rolesArray)) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'required_roles' => $rolesArray,
                    'user_role' => $user->rol_id
                ], 403);
            }
        }

        // Asignar el usuario a la request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
