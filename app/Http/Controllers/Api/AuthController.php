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
            'rol_id' => $request->rol_id ?? 4,
        ]);

        $token = $usuario->createToken('API Token')->plainTextToken;

        return response()->json([
            'usuario' => $usuario,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        // Manejar posibles problemas de UTF-8 parseando manualmente el JSON
        $data = json_decode($request->getContent(), true) ?? $request->all();
        
        $correo = $data['correo_electronico'] ?? $request->input('correo_electronico');
        $password = $data['contraseña'] ?? $data['password'] ?? $request->input('contraseña') ?? $request->input('password');

        if (!$correo || !$password) {
            throw ValidationException::withMessages([
                'correo_electronico' => ['El correo electrónico es requerido.'],
                'contraseña' => ['La contraseña es requerida.'],
            ]);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'correo_electronico' => ['El correo electrónico debe ser válido.'],
            ]);
        }

        $usuario = Usuario::where('correo_electronico', $correo)->first();

        if (! $usuario || ! Hash::check($password, $usuario->contraseña)) {
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

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'nombre' => 'required|string|max:50',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'nullable|string|max:50',
            'correo_electronico' => 'required|email|unique:usuarios,correo_electronico,' . $user->id,
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:Masculino,Femenino,Otro',
            'curp' => 'nullable|string|max:18|unique:usuarios,curp,' . $user->id . ',id',
            'ocupacion' => 'nullable|string|max:100',
        ], [], [
            'correo_electronico' => 'correo electrónico',
        ]);

        // Preparar los datos para actualización, convirtiendo strings vacíos a null
        $updateData = [];
        $fields = ['nombre', 'apellido_paterno', 'apellido_materno', 'correo_electronico', 'telefono', 'direccion', 'fecha_nacimiento', 'sexo', 'curp', 'ocupacion'];
        
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                $updateData[$field] = $value === '' ? null : $value;
            }
        }
        
        $user->update($updateData);

        return response()->json([
            'usuario' => $user->fresh(),
            'mensaje' => 'Perfil actualizado correctamente',
        ]);
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'credential' => 'required|string',
        ]);

        try {
            // Decodificar el JWT de Google (sin verificar firma para desarrollo)
            $credential = $request->credential;
            $parts = explode('.', $credential);
            
            if (count($parts) !== 3) {
                throw new \Exception('Token inválido');
            }
            
            $payload = json_decode(base64_decode($parts[1]), true);
            
            if (!$payload || !isset($payload['email'])) {
                throw new \Exception('Payload inválido');
            }

            $email = $payload['email'];
            $name = $payload['name'] ?? '';
            $given_name = $payload['given_name'] ?? '';
            $family_name = $payload['family_name'] ?? '';

            // Buscar usuario existente
            $usuario = Usuario::where('correo_electronico', $email)->first();

            if (!$usuario) {
                // Crear nuevo usuario si no existe
                $usuario = Usuario::create([
                    'nombre' => $given_name,
                    'apellido_paterno' => $family_name,
                    'apellido_materno' => '',
                    'correo_electronico' => $email,
                    'contraseña' => bcrypt(uniqid()), // Contraseña aleatoria
                    'telefono' => '',
                    'direccion' => '',
                    'fecha_nacimiento' => now()->subYears(25)->toDateString(),
                    'sexo' => 'Otro',
                    'curp' => '',
                    'ocupacion' => '',
                    'estatus' => 'activo',
                    'rol_id' => 4, // Paciente por defecto
                ]);
            }

            $token = $usuario->createToken('Google API Token')->plainTextToken;

            return response()->json([
                'usuario' => $usuario,
                'token' => $token,
                'mensaje' => 'Login con Google exitoso',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar login con Google: ' . $e->getMessage(),
            ], 400);
        }
    }
}
