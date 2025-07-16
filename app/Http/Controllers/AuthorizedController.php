<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;

/**
 * Controlador base que maneja autorización de roles
 */
class AuthorizedController extends Controller
{
    /**
     * Autenticar usuario desde el token Bearer
     */
    protected function authenticateUser(Request $request)
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
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
            
            return $user;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verificar si el usuario tiene acceso basado en su rol
     */
    protected function checkRoleAccess(Request $request, array $allowedRoles)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        if (!in_array($user->rol_id, $allowedRoles)) {
            return response()->json([
                'error' => 'Acceso denegado',
                'required_roles' => $allowedRoles,
                'user_role' => $user->rol_id,
                'message' => 'No tienes permisos para realizar esta acción'
            ], 403);
        }

        return null; // Sin errores, el usuario tiene acceso
    }

    /**
     * Verificar si el usuario es administrador
     */
    protected function requireAdmin(Request $request)
    {
        return $this->checkRoleAccess($request, [1]);
    }

    /**
     * Verificar si el usuario es admin o recepcionista
     */
    protected function requireAdminOrReceptionist(Request $request)
    {
        return $this->checkRoleAccess($request, [1, 3]);
    }

    /**
     * Verificar si el usuario es admin, recepcionista o terapeuta
     */
    protected function requireStaff(Request $request)
    {
        return $this->checkRoleAccess($request, [1, 2, 3]);
    }

    /**
     * Verificar si el usuario es paciente
     */
    protected function requirePatient(Request $request)
    {
        return $this->checkRoleAccess($request, [4]);
    }

    /**
     * Verificar si el usuario es terapeuta
     */
    protected function requireTherapist(Request $request)
    {
        return $this->checkRoleAccess($request, [2]);
    }

    /**
     * Cualquier usuario autenticado
     */
    protected function requireAuth(Request $request)
    {
        return $this->checkRoleAccess($request, [1, 2, 3, 4]);
    }
}