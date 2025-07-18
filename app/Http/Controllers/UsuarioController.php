<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function __construct()
    {
        // El middleware auth:sanctum y role:1 ya están configurados en las rutas
    }

    public function index(Request $request)
    {
        return response()->json(Usuario::all(), 200);
    }

    public function store(StoreUsuarioRequest $request)
    {
        $data = $request->validated();
        $data['contraseña'] = bcrypt($data['contraseña']);

        return response()->json(Usuario::create($data), 201);
    }

    public function show(Request $request, Usuario $usuario)
    {
        return response()->json($usuario, 200);
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        $data = $request->validated();
        if (isset($data['contraseña'])) {
            $data['contraseña'] = bcrypt($data['contraseña']);
        }
        $usuario->update($data);

        return response()->json($usuario, 200);
    }

    public function destroy(Request $request, Usuario $usuario)
    {

        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente'], 200);
    }
}
