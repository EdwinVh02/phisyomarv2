<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use Illuminate\Http\Request;

class UsuarioController extends AuthorizedController
{
    public function __construct()
    {
        // Ya no necesitamos middleware aquí porque está en las rutas
    }

    public function index(Request $request)
    {
        // Autenticación manual
        $user = $this->authenticateUser($request);
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        // Solo administradores pueden ver todos los usuarios
        if ($user->rol_id !== 1) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        return response()->json(Usuario::all(), 200);
    }

    public function store(StoreUsuarioRequest $request)
    {
        // Solo administradores pueden crear usuarios
        $error = $this->requireAdmin($request);
        if ($error) return $error;

        $data = $request->validated();
        $data['contraseña'] = bcrypt($data['contraseña']);
        return response()->json(Usuario::create($data), 201);
    }

    public function show(Request $request, Usuario $usuario)
    {
        // Solo administradores pueden ver detalles de usuarios
        $error = $this->requireAdmin($request);
        if ($error) return $error;

        return response()->json($usuario, 200);
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        // Solo administradores pueden actualizar usuarios
        $error = $this->requireAdmin($request);
        if ($error) return $error;

        $data = $request->validated();
        if (isset($data['contraseña'])) {
            $data['contraseña'] = bcrypt($data['contraseña']);
        }
        $usuario->update($data);
        return response()->json($usuario, 200);
    }

    public function destroy(Request $request, Usuario $usuario)
    {
        // Solo administradores pueden eliminar usuarios
        $error = $this->requireAdmin($request);
        if ($error) return $error;

        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
    }
}
