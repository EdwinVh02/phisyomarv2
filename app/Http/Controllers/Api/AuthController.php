<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Services\UserRoleRegistrationService;
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
            'rol_id' => $request->rol_id ?? 4, // Por defecto paciente
        ]);

        // Crear automáticamente el registro específico según el rol
        UserRoleRegistrationService::createRoleSpecificRecord($usuario);

        $token = $usuario->createToken('API Token')->plainTextToken;

        // Obtener datos completos del perfil
        $profileData = UserRoleRegistrationService::getUserProfileData($usuario);

        return response()->json([
            'usuario' => $usuario->fresh()->load(['rol', 'paciente', 'terapeuta', 'recepcionista', 'administrador']),
            'token' => $token,
            'profile_complete' => $profileData['profile_complete'],
            'missing_fields' => $profileData['missing_fields'],
            'role_name' => $profileData['role_name'],
        ], 201);
    }

    public function login(Request $request)
    {
        // Log inicial con TODA la información
        error_log('=== INICIO LOGIN DEBUG ===');
        error_log('REQUEST METHOD: ' . $request->method());
        error_log('REQUEST URL: ' . $request->fullUrl());
        error_log('REQUEST HEADERS: ' . json_encode($request->headers->all()));
        error_log('REQUEST BODY RAW: ' . $request->getContent());
        error_log('REQUEST ALL: ' . json_encode($request->all()));
        
        try {
            \Illuminate\Support\Facades\Log::info('Login attempt started', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => $request->except(['contraseña', 'password'])
            ]);

            error_log('Paso 1: Parseando datos del request...');
            // Manejar posibles problemas de UTF-8 parseando manualmente el JSON
            $data = json_decode($request->getContent(), true) ?? $request->all();
            error_log('Datos parseados: ' . json_encode($data));
            
            $correo = $data['correo_electronico'] ?? $request->input('correo_electronico');
            $password = $data['contraseña'] ?? $data['password'] ?? $request->input('contraseña') ?? $request->input('password');
            
            error_log('Email extraído: ' . ($correo ?? 'NULL'));
            error_log('Password presente: ' . ($password ? 'SÍ' : 'NO'));

            if (!$correo || !$password) {
                error_log('ERROR: Credenciales faltantes - Email: ' . ($correo ?? 'NULL') . ', Password: ' . ($password ? 'presente' : 'ausente'));
                \Illuminate\Support\Facades\Log::warning('Login failed: missing credentials');
                throw ValidationException::withMessages([
                    'correo_electronico' => ['El correo electrónico es requerido.'],
                    'contraseña' => ['La contraseña es requerida.'],
                ]);
            }

            error_log('Paso 2: Validando formato de email...');
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                error_log('ERROR: Email inválido - ' . $correo);
                \Illuminate\Support\Facades\Log::warning('Login failed: invalid email format', ['email' => $correo]);
                throw ValidationException::withMessages([
                    'correo_electronico' => ['El correo electrónico debe ser válido.'],
                ]);
            }

            error_log('Paso 3: Buscando usuario en BD...');
            $usuario = Usuario::where('correo_electronico', $correo)->first();
            error_log('Usuario encontrado: ' . ($usuario ? 'SÍ (ID: ' . $usuario->id . ', Rol: ' . $usuario->rol_id . ')' : 'NO'));

            if (!$usuario) {
                error_log('ERROR: Usuario no encontrado para email: ' . $correo);
                \Illuminate\Support\Facades\Log::warning('Login failed: user not found', ['email' => $correo]);
                throw ValidationException::withMessages([
                    'correo_electronico' => ['Estas credenciales no coinciden con nuestros registros.'],
                ]);
            }

            error_log('Paso 4: Verificando contraseña...');
            if (!Hash::check($password, $usuario->contraseña)) {
                error_log('ERROR: Contraseña incorrecta para usuario ID: ' . $usuario->id);
                \Illuminate\Support\Facades\Log::warning('Login failed: invalid password', ['user_id' => $usuario->id, 'email' => $correo]);
                throw ValidationException::withMessages([
                    'correo_electronico' => ['Estas credenciales no coinciden con nuestros registros.'],
                ]);
            }

            error_log('✓ ÉXITO: Usuario autenticado - ID: ' . $usuario->id . ', Rol: ' . $usuario->rol_id);
            \Illuminate\Support\Facades\Log::info('User authenticated successfully', ['user_id' => $usuario->id, 'role_id' => $usuario->rol_id]);

            // Crear automáticamente el registro específico si no existe
            error_log('Paso 5: Ejecutando UserRoleRegistrationService::createRoleSpecificRecord...');
            try {
                $roleServiceResult = UserRoleRegistrationService::createRoleSpecificRecord($usuario);
                error_log('✓ UserRoleRegistrationService::createRoleSpecificRecord exitoso: ' . ($roleServiceResult ? 'true' : 'false'));
                \Illuminate\Support\Facades\Log::info('Role specific record creation', ['result' => $roleServiceResult]);
            } catch (\Exception $e) {
                error_log('ERROR en UserRoleRegistrationService::createRoleSpecificRecord: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
                \Illuminate\Support\Facades\Log::error('Error creating role specific record', [
                    'user_id' => $usuario->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // No lanzar error aquí, continuar con el login
            }

            error_log('Paso 6: Creando token de acceso...');
            $token = $usuario->createToken('API Token')->plainTextToken;
            error_log('✓ Token creado exitosamente');
            \Illuminate\Support\Facades\Log::info('Token created successfully', ['user_id' => $usuario->id]);

            // Obtener datos completos del perfil
            error_log('Paso 7: Ejecutando UserRoleRegistrationService::getUserProfileData...');
            try {
                $profileData = UserRoleRegistrationService::getUserProfileData($usuario);
                error_log('✓ getUserProfileData exitoso - role_name: ' . ($profileData['role_name'] ?? 'N/A'));
                \Illuminate\Support\Facades\Log::info('Profile data obtained', ['user_id' => $usuario->id]);
            } catch (\Exception $e) {
                error_log('ERROR en UserRoleRegistrationService::getUserProfileData: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
                \Illuminate\Support\Facades\Log::error('Error getting profile data', [
                    'user_id' => $usuario->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Fallback con datos básicos
                $profileData = [
                    'profile_complete' => false,
                    'missing_fields' => [],
                    'role_name' => $usuario->rol->nombre ?? 'Desconocido'
                ];
                error_log('✓ Usando fallback data - role_name: ' . $profileData['role_name']);
            }

            error_log('Paso 8: Cargando relaciones del usuario...');
            try {
                $usuarioConRelaciones = $usuario->fresh()->load(['rol', 'paciente', 'terapeuta', 'recepcionista', 'administrador']);
                error_log('✓ Relaciones cargadas exitosamente');
            } catch (\Exception $e) {
                error_log('ERROR cargando relaciones: ' . $e->getMessage());
                $usuarioConRelaciones = $usuario;
            }

            $response = [
                'usuario' => $usuarioConRelaciones,
                'token' => $token,
                'profile_complete' => $profileData['profile_complete'],
                'missing_fields' => $profileData['missing_fields'],
                'role_name' => $profileData['role_name'],
            ];

            error_log('Paso 9: Preparando respuesta final...');
            error_log('Response keys: ' . implode(', ', array_keys($response)));
            \Illuminate\Support\Facades\Log::info('Login completed successfully', ['user_id' => $usuario->id]);
            
            error_log('=== FIN LOGIN DEBUG - ÉXITO ===');
            return response()->json($response);

        } catch (ValidationException $e) {
            error_log('=== VALIDATION EXCEPTION ===');
            error_log('Validation errors: ' . json_encode($e->errors()));
            \Illuminate\Support\Facades\Log::warning('Login validation failed', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            error_log('=== EXCEPTION CRÍTICA EN LOGIN ===');
            error_log('Error: ' . $e->getMessage());
            error_log('Archivo: ' . $e->getFile());
            error_log('Línea: ' . $e->getLine());
            error_log('Stack trace: ' . $e->getTraceAsString());
            error_log('=== FIN EXCEPTION CRÍTICA ===');
            
            \Illuminate\Support\Facades\Log::error('Unexpected login error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => config('app.debug') ? $e->getMessage() : 'Por favor, inténtalo de nuevo más tarde',
                'debug' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'Sesión cerrada correctamente.']);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        
        // Crear automáticamente el registro específico si no existe
        UserRoleRegistrationService::createRoleSpecificRecord($user);
        
        // Obtener datos completos del perfil
        $profileData = UserRoleRegistrationService::getUserProfileData($user);
        
        // Cargar relaciones específicas según el rol
        $user = $user->fresh()->load(['rol']);
        
        switch ($user->rol_id) {
            case 4: // Paciente
                $user->load('paciente');
                break;
            case 2: // Terapeuta
                $user->load(['terapeuta', 'terapeuta.especialidades']);
                break;
            case 3: // Recepcionista
                $user->load('recepcionista');
                break;
            case 1: // Administrador
                $user->load(['administrador', 'administrador.clinica']);
                break;
        }
        
        return response()->json([
            'user' => $user,
            'role_name' => $profileData['role_name'],
            'profile_complete' => $profileData['profile_complete'],
            'missing_fields' => $profileData['missing_fields'],
            'permissions' => [
                'can_manage_patients' => $user->canManagePatients(),
                'can_view_stats' => $user->canViewGeneralStats(),
                'can_access_financials' => $user->canAccessFinancials(),
            ]
        ]);
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
                
                // Crear automáticamente el registro específico según el rol
                UserRoleRegistrationService::createRoleSpecificRecord($usuario);
            }

            // Asegurar que existe el registro específico del rol
            UserRoleRegistrationService::createRoleSpecificRecord($usuario);

            $token = $usuario->createToken('Google API Token')->plainTextToken;
            
            // Obtener datos completos del perfil
            $profileData = UserRoleRegistrationService::getUserProfileData($usuario);

            return response()->json([
                'usuario' => $usuario->fresh()->load(['rol', 'paciente', 'terapeuta', 'recepcionista', 'administrador']),
                'token' => $token,
                'profile_complete' => $profileData['profile_complete'],
                'missing_fields' => $profileData['missing_fields'],
                'role_name' => $profileData['role_name'],
                'mensaje' => 'Login con Google exitoso',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar login con Google: ' . $e->getMessage(),
            ], 400);
        }
    }
}
