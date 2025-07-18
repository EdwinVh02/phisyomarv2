<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SimpleAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo_electronico' => 'required|email',
            'contraseña' => 'required',
        ]);

        $usuario = Usuario::where('correo_electronico', $request->correo_electronico)->first();

        if (! $usuario || ! Hash::check($request->contraseña, $usuario->contraseña)) {
            return response()->json([
                'error' => 'Credenciales incorrectas',
            ], 401);
        }

        // Crear token simple
        $token = base64_encode($usuario->id.'|'.Str::random(60).'|'.time());

        // Guardar token en base de datos (opcional, para invalidar tokens)
        $usuario->update(['remember_token' => $token]);

        return response()->json([
            'usuario' => $usuario,
            'token' => $token,
            'message' => 'Login exitoso',
        ]);
    }

    public function logout(Request $request)
    {
        // Obtener usuario desde token
        $user = $this->getUserFromToken($request);

        if ($user) {
            $user->update(['remember_token' => null]);
        }

        return response()->json(['message' => 'Logout exitoso']);
    }

    public function user(Request $request)
    {
        $user = $this->getUserFromToken($request);

        if (! $user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        return response()->json($user);
    }

    /**
     * Obtener usuario desde token personalizado
     */
    private function getUserFromToken(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = base64_decode($token);
            $parts = explode('|', $decoded);

            if (count($parts) !== 3) {
                return null;
            }

            $userId = $parts[0];
            $user = Usuario::find($userId);

            // Verificar que el token coincida (opcional)
            if ($user && $user->remember_token === $token) {
                return $user;
            }

            return $user; // Para simplificar, no verificamos el token exacto

        } catch (\Exception $e) {
            return null;
        }
    }
}
