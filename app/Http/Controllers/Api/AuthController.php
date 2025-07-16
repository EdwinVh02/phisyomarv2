<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'required|string|max:50',
            'correo_electronico' => 'required|email|unique:usuarios,correo_electronico',
            'contraseña' => 'required|string|min:8|confirmed',
            'telefono' => 'required|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'required|in:Masculino,Femenino,Otro',
            'curp' => 'required|string|size:18|unique:usuarios,curp',
            'ocupacion' => 'nullable|string|max:100',
            'estatus' => 'nullable|in:activo,inactivo,suspendido',
            'rol_id' => 'nullable|integer|exists:roles,id',
        ], [], [
            'contraseña' => 'contraseña',
            'correo_electronico' => 'correo electrónico',
        ]);

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'correo_electronico' => $request->correo_electronico,
            'contraseña' => $request->contraseña,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo' => $request->sexo,
            'curp' => $request->curp,
            'ocupacion' => $request->ocupacion,
            'estatus' => $request->estatus ?? 'activo',
            'rol_id' => $request->rol_id ?? 4
        ]);


        $token = $usuario->createToken('API Token')->plainTextToken;

        return response()->json([
            'usuario' => $usuario,
            'token' => $token,
        ], 201);
    }


    public function login(Request $request)
    {
        $request->validate([
            'correo_electronico' => 'required|email',
            'contraseña' => 'required',
        ], [], [
            'correo_electronico' => 'correo electrónico',
            'contraseña' => 'contraseña',
        ]);

        $usuario = Usuario::where('correo_electronico', $request->correo_electronico)->first();

        if (!$usuario || !Hash::check($request->contraseña, $usuario->contraseña)) {
            throw ValidationException::withMessages([
                'correo_electronico' => ['Estas credenciales no coinciden con nuestros registros.'],
            ]);
        }

        $token = $usuario->createToken('API Token')->plainTextToken;
        
        return response()->json([
            'usuario' => $usuario,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['mensaje' => 'Sesión cerrada correctamente.']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
