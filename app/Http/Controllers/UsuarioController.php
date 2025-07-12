<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index()
    {
        return response()->json(Usuario::all(), 200);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nombre'            => 'required|string|max:100',
            'apellido_paterno'  => 'required|string|max:100',
            'apellido_materno'  => 'nullable|string|max:100',
            'correo_electronico'             => 'required|email|unique:usuarios,email',
            'telefono'          => 'nullable|string|max:20',
            'contraseña'          => 'required|string|min:6',
            'rol_id'            => 'required|exists:rols,id',
            'estatus'           => 'nullable|in:activo,inactivo', // o integer si tu tabla lo maneja como 1/0
        ]);
        $data['contraseña'] = bcrypt($data['contraseña']);
        return response()->json(Usuario::create($data), 201);
    }

    public function show(Usuario $usuario)
    {
        return response()->json($usuario, 200);
    }

    public function update(Request $r, Usuario $usuario)
    {
        $data = $r->validate([
            'nombre'            => 'sometimes|string|max:100',
            'apellido_paterno'  => 'sometimes|string|max:100',
            'apellido_materno'  => 'nullable|string|max:100',
            'correo_electronico' => 'sometimes|email|unique:usuarios,email,' . $usuario->id,
            'telefono'          => 'nullable|string|max:20',
            'contraseña'          => 'sometimes|string|min:6',
            'rol_id'            => 'sometimes|exists:rols,id',
            'estatus'           => 'nullable|in:activo,inactivo',
            'nombre',
        ]);
        if (isset($data['contraseña'])) {
            $data['contraseña'] = bcrypt($data['contraseña']);
        }
        $usuario->update($data);
        return response()->json($usuario, 200);
    }

    public function destroy(Usuario $usuario)
    {
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
    }
}
