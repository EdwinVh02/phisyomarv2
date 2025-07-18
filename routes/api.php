<?php

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AdjuntoController;
use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AtiendeController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ClinicaController;
use App\Http\Controllers\ConsentimientoInformadoController;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\HistorialMedicoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PadecimientoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PaquetePacienteController;
use App\Http\Controllers\PaqueteSesionController;
use App\Http\Controllers\PreguntaController;
use App\Http\Controllers\RecepcionistaController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\RespuestaController;
use App\Http\Controllers\SmartwatchController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\TarjetaController;
use App\Http\Controllers\TerapeutaController;
use App\Http\Controllers\TratamientoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ValoracionController;
use Illuminate\Support\Facades\Route;

// ==========================================
// RUTAS PÚBLICAS (Sin autenticación)
// ==========================================

// Autenticación
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('simple-login', [\App\Http\Controllers\Api\SimpleAuthController::class, 'login']);
Route::post('auth/google', [AuthController::class, 'googleLogin']);

// Datos de consulta pública
Route::apiResource('especialidades', EspecialidadController::class)->only(['index', 'show']);
Route::apiResource('padecimientos', PadecimientoController::class)->only(['index', 'show']);
Route::apiResource('tarifas', TarifaController::class)->only(['index', 'show']);
Route::apiResource('tratamientos', TratamientoController::class)->only(['index', 'show']);

// Sistema de disponibilidad de citas
Route::prefix('citas')->group(function () {
    Route::post('calendario-disponibilidad', [CitaController::class, 'calendarioDisponibilidad']);
    Route::post('horas-disponibles', [CitaController::class, 'horasDisponibles']);
    Route::post('fechas-disponibles', [CitaController::class, 'fechasDisponibles']);
});

// Información pública de terapeutas para pacientes
Route::get('terapeutas-publico', function () {
    try {
        $terapeutas = \App\Models\Terapeuta::with('usuario')->get()->map(function ($terapeuta) {
            return [
                'id' => $terapeuta->id,
                'numero_cedula' => $terapeuta->numero_cedula,
                'telefono_consultorio' => $terapeuta->telefono_consultorio,
                'especialidad_principal' => 'Fisioterapia',
                'usuario' => [
                    'nombre' => $terapeuta->usuario->nombre ?? 'Sin nombre',
                    'apellido_paterno' => $terapeuta->usuario->apellido_paterno ?? '',
                    'apellido_materno' => $terapeuta->usuario->apellido_materno ?? '',
                ],
            ];
        });
        return response()->json($terapeutas);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al obtener terapeutas: ' . $e->getMessage(),
        ], 500);
    }
});

// ==========================================
// RUTAS PROTEGIDAS (Con autenticación)
// ==========================================

Route::middleware('auth:sanctum')->group(function () {
    
    // Perfil del usuario autenticado
    Route::get('user', [AuthController::class, 'user']);
    Route::put('user/profile', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);
    
    // ==========================================
    // RECURSOS ADMINISTRATIVOS
    // ==========================================
    Route::middleware('role:1')->group(function () {
        Route::apiResource('usuarios', UsuarioController::class);
        Route::apiResource('administradores', AdministradorController::class);
        Route::apiResource('clinicas', ClinicaController::class);
        Route::apiResource('bitacoras', BitacoraController::class);
        Route::apiResource('registros', RegistroController::class);
    });
    
    // ==========================================
    // RECURSOS DE GESTIÓN MÉDICA
    // ==========================================
    Route::middleware('role:1,2,3')->group(function () {
        Route::apiResource('pacientes', PacienteController::class);
        Route::apiResource('citas', CitaController::class);
        Route::apiResource('terapeutas', TerapeutaController::class);
        Route::apiResource('recepcionistas', RecepcionistaController::class);
        Route::apiResource('historiales', HistorialMedicoController::class);
        Route::apiResource('consentimientos', ConsentimientoInformadoController::class);
        Route::apiResource('valoraciones', ValoracionController::class);
        Route::apiResource('encuestas', EncuestaController::class);
        Route::apiResource('preguntas', PreguntaController::class);
        Route::apiResource('respuestas', RespuestaController::class);
        
        // Nuevas rutas agregadas
        Route::apiResource('adjuntos', AdjuntoController::class);
        Route::apiResource('atiende', AtiendeController::class);
        Route::apiResource('paquetes-paciente', PaquetePacienteController::class);
        Route::apiResource('paquetes-sesion', PaqueteSesionController::class);
    });
    
    // ==========================================
    // RECURSOS FINANCIEROS
    // ==========================================
    Route::middleware('role:1,3')->group(function () {
        Route::apiResource('pagos', PagoController::class);
        Route::apiResource('tarjetas', TarjetaController::class);
    });
    
    // ==========================================
    // TECNOLOGÍA MÉDICA
    // ==========================================
    Route::middleware('role:1,2')->group(function () {
        Route::apiResource('smartwatches', SmartwatchController::class);
    });
});

// ==========================================
// RUTAS ESPECÍFICAS POR ROL
// ==========================================

// Rutas específicas para pacientes
Route::prefix('paciente')->middleware(['auth:sanctum', 'role:4'])->group(function () {
    Route::get('mis-citas', [CitaController::class, 'misCitas']);
    Route::get('cita/{id}', [CitaController::class, 'miCitaDetalle']);
    Route::get('mi-historial', [HistorialMedicoController::class, 'miHistorial']);
    Route::get('mis-pagos', [PagoController::class, 'misPagos']);
    Route::get('mis-encuestas', [EncuestaController::class, 'misEncuestas']);
    Route::post('responder-encuesta/{citaId}', [EncuestaController::class, 'responderEncuesta']);
    Route::post('agendar-cita', [CitaController::class, 'agendarCita']);
    Route::put('cancelar-cita/{id}', [CitaController::class, 'cancelarCita']);
});

// Rutas específicas para terapeutas
Route::prefix('terapeuta')->middleware(['auth:sanctum', 'role:2'])->group(function () {
    Route::get('mis-citas', [CitaController::class, 'misCitasTerapeuta']);
    Route::get('mis-pacientes', [PacienteController::class, 'misPacientes']);
    Route::get('estadisticas', [RegistroController::class, 'estadisticasTerapeuta']);
});

// Rutas específicas para recepcionistas
Route::prefix('recepcionista')->middleware(['auth:sanctum', 'role:3'])->group(function () {
    Route::get('citas-hoy', [CitaController::class, 'citasHoy']);
    Route::post('registrar-llegada/{cita}', [CitaController::class, 'registrarLlegada']);
});

// ==========================================
// RUTAS DE DESARROLLO Y TESTING
// ==========================================
if (app()->environment(['local', 'testing'])) {
    
    // Rutas básicas de prueba
    Route::get('prueba', function () {
        return response()->json(['mensaje' => 'API funcionando correctamente']);
    });
    
    // Debug de base de datos
    Route::get('debug-tables', function () {
        return response()->json([
            'usuarios_count' => \App\Models\Usuario::count(),
            'pacientes_count' => \App\Models\Paciente::count(),
            'terapeutas_count' => \App\Models\Terapeuta::count(),
            'usuarios' => \App\Models\Usuario::all(),
            'pacientes' => \App\Models\Paciente::all(),
            'terapeutas' => \App\Models\Terapeuta::all(),
        ]);
    });
    
    // Rutas de prueba para citas
    Route::prefix('test')->group(function () {
        Route::get('citas', function () {
            return response()->json(\App\Models\Cita::with(['paciente.usuario', 'terapeuta.usuario'])->get());
        });
        
        Route::get('usuarios', function () {
            return response()->json(\App\Models\Usuario::all());
        });
        
        Route::get('pacientes', function () {
            return response()->json(\App\Models\Paciente::with('usuario')->get());
        });
        
        Route::get('terapeutas', function () {
            return response()->json(\App\Models\Terapeuta::with('usuario')->get());
        });
        
        // Login temporal sin middleware para pruebas
        Route::post('login', function (\Illuminate\Http\Request $request) {
            $correo = $request->input('correo_electronico');
            $password = $request->input('contraseña');

            $usuario = \App\Models\Usuario::where('correo_electronico', $correo)->first();

            if (!$usuario) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            if (!\Illuminate\Support\Facades\Hash::check($password, $usuario->contraseña)) {
                return response()->json(['error' => 'Contraseña incorrecta'], 401);
            }

            $token = $usuario->createToken('API Token')->plainTextToken;

            return response()->json([
                'success' => true,
                'usuario' => $usuario,
                'token' => $token,
                'mensaje' => 'Login exitoso',
            ]);
        });
    });
    
    // Herramientas de desarrollo adicionales
    Route::prefix('dev')->group(function () {
        // Crear datos de prueba rápidos
        Route::post('setup-data', function () {
            try {
                // Crear usuarios básicos para testing
                $usuarioTerapeuta = \App\Models\Usuario::create([
                    'nombre' => 'Dr. Juan',
                    'apellido_paterno' => 'González',
                    'correo_electronico' => 'terapeuta@test.com',
                    'contraseña' => bcrypt('password123'),
                    'telefono' => '5551111111',
                    'rol_id' => 2
                ]);

                $terapeuta = \App\Models\Terapeuta::create([
                    'id' => $usuarioTerapeuta->id,
                    'numero_cedula' => '1234567890'
                ]);

                $usuarioPaciente = \App\Models\Usuario::create([
                    'nombre' => 'María',
                    'apellido_paterno' => 'López',
                    'correo_electronico' => 'paciente@test.com',
                    'contraseña' => bcrypt('password123'),
                    'telefono' => '5552222222',
                    'rol_id' => 4
                ]);

                $paciente = \App\Models\Paciente::create([
                    'id' => $usuarioPaciente->id,
                    'contacto_emergencia_nombre' => 'José López',
                    'contacto_emergencia_telefono' => '5553333333'
                ]);

                return response()->json([
                    'mensaje' => 'Datos de prueba creados exitosamente',
                    'terapeuta' => $terapeuta->load('usuario'),
                    'paciente' => $paciente->load('usuario')
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Error al crear datos: ' . $e->getMessage()
                ], 500);
            }
        });

        // Operaciones específicas de citas para testing
        Route::post('crear-cita', function (\Illuminate\Http\Request $request) {
            try {
                $cita = \App\Models\Cita::create([
                    'fecha_hora' => $request->fecha_hora ?? now()->addDays(1),
                    'tipo' => $request->tipo ?? 'Consulta',
                    'duracion' => $request->duracion ?? 60,
                    'motivo' => $request->motivo ?? 'Consulta de prueba',
                    'estado' => 'agendada',
                    'paciente_id' => $request->paciente_id,
                    'terapeuta_id' => $request->terapeuta_id,
                ]);

                return response()->json([
                    'mensaje' => 'Cita creada exitosamente',
                    'cita' => $cita->load(['paciente.usuario', 'terapeuta.usuario'])
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Error al crear cita: ' . $e->getMessage()
                ], 500);
            }
        });

        // Endpoint de diagnóstico para verificar autenticación
        Route::post('test-auth', function (\Illuminate\Http\Request $request) {
            try {
                $user = $request->user();
                $token = $request->bearerToken();
                
                return response()->json([
                    'authenticated' => $user ? true : false,
                    'user' => $user,
                    'token_present' => $token ? true : false,
                    'token_preview' => $token ? substr($token, 0, 20) . '...' : null,
                    'headers' => $request->headers->all()
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Error en diagnóstico: ' . $e->getMessage()
                ], 500);
            }
        });

        // Endpoint temporal para probar agendar cita sin autenticación
        Route::post('agendar-cita-temp', function (\Illuminate\Http\Request $request) {
            try {
                // Usar paciente ID 200 y terapeuta ID 100 por defecto
                $cita = \App\Models\Cita::create([
                    'fecha_hora' => $request->fecha_hora,
                    'tipo' => $request->tipo ?? 'Consulta',
                    'duracion' => $request->duracion ?? 60,
                    'motivo' => $request->motivo ?? 'Consulta de prueba',
                    'estado' => 'agendada',
                    'paciente_id' => 200, // Paciente de prueba
                    'terapeuta_id' => $request->terapeuta_id ?? 100,
                    'ubicacion' => $request->ubicacion,
                    'equipo_asignado' => $request->equipo_asignado,
                    'observaciones' => $request->observaciones,
                    'escala_dolor_eva_inicio' => $request->escala_dolor_eva_inicio,
                    'como_fue_lesion' => $request->como_fue_lesion,
                    'antecedentes_patologicos' => $request->antecedentes_patologicos,
                    'antecedentes_no_patologicos' => $request->antecedentes_no_patologicos,
                ]);

                return response()->json([
                    'mensaje' => 'Cita agendada exitosamente (modo temporal)',
                    'cita' => $cita->load(['paciente.usuario', 'terapeuta.usuario'])
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Error al agendar cita: ' . $e->getMessage()
                ], 500);
            }
        });

        Route::get('mis-citas/{pacienteId}', function ($pacienteId) {
            try {
                $citas = \App\Models\Cita::where('paciente_id', $pacienteId)
                    ->with(['terapeuta.usuario'])
                    ->orderBy('fecha_hora', 'desc')
                    ->get();

                return response()->json($citas);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Error al obtener citas: ' . $e->getMessage()
                ], 500);
            }
        });
    });
}
