<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Symfony\Component\HttpFoundation\Response;

class SimpleAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        $token = substr($authHeader, 7);
        
        try {
            $decoded = base64_decode($token);
            $parts = explode('|', $decoded);
            
            if (count($parts) !== 3) {
                return response()->json(['error' => 'Token inválido'], 401);
            }

            $userId = $parts[0];
            $user = Usuario::find($userId);
            
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 401);
            }

            // Agregar el usuario a la request
            $request->merge(['authenticated_user' => $user]);
            
            // También configurar para que funcione con request()->user()
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            return $next($request);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token inválido'], 401);
        }
    }
}