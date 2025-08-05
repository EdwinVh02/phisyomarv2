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
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileCompletionController;
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

// Paquetes públicos para consulta de precios
Route::get('paquetes-publico', function () {
    try {
        $paquetes = \App\Models\PaqueteSesion::all();
        return response()->json($paquetes);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al obtener paquetes: ' . $e->getMessage(),
        ], 500);
    }
});

Route::get('tarifas-publico', function () {
    try {
        $tarifas = \App\Models\Tarifa::all();
        return response()->json($tarifas);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al obtener tarifas: ' . $e->getMessage(),
        ], 500);
    }
});

// Sistema de disponibilidad de citas
Route::prefix('citas')->group(function () {
    Route::post('calendario-disponibilidad', [CitaController::class, 'calendarioDisponibilidad']);
    Route::post('horas-disponibles', [CitaController::class, 'horasDisponibles']);
    Route::post('fechas-disponibles', [CitaController::class, 'fechasDisponibles']);
});

// Debug de horarios disponibles
Route::get('debug-horarios/{fecha}/{terapeutaId}', function ($fecha, $terapeutaId) {
    try {
        $debug = \App\Models\Cita::debugHorasDisponibles($fecha, $terapeutaId, 60);
        $horasDisponibles = \App\Models\Cita::horasDisponibles($fecha, $terapeutaId, 60);
        
        return response()->json([
            'fecha' => $fecha,
            'terapeuta_id' => $terapeutaId,
            'horas_disponibles_finales' => $horasDisponibles,
            'debug_detallado' => $debug,
            'total_disponibles' => count($horasDisponibles),
            'hora_servidor' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
            'timezone_servidor' => config('app.timezone')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error en debug: ' . $e->getMessage()
        ], 500);
    }
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
    // GESTIÓN DE PERFILES ESPECÍFICOS POR ROL
    // ==========================================
    Route::prefix('user/profile')->group(function () {
        Route::get('check-completeness', [ProfileCompletionController::class, 'checkCompleteness']);
        Route::get('data', [ProfileCompletionController::class, 'getProfileData']);
        Route::get('missing-fields', [ProfileCompletionController::class, 'getMissingFields']);
        Route::post('complete', [ProfileCompletionController::class, 'completeProfile']);
        Route::patch('update-field', [ProfileCompletionController::class, 'updateProfileField']);
    });
    
    // ==========================================
    // RECURSOS ADMINISTRATIVOS
    // ==========================================
    Route::middleware('role:1')->group(function () {
        Route::apiResource('usuarios', UsuarioController::class);
        Route::apiResource('administradores', AdministradorController::class);
        Route::apiResource('clinicas', ClinicaController::class);
        Route::apiResource('bitacoras', BitacoraController::class);
        Route::apiResource('registros', RegistroController::class);
        
        // Rutas de estadísticas y analytics
        Route::prefix('estadisticas')->group(function () {
            Route::get('dashboard', [EstadisticaController::class, 'dashboard']);
            Route::get('kpis/{timeRange?}', [EstadisticaController::class, 'getKPIs']);
            Route::get('citas-por-mes/{timeRange?}', [EstadisticaController::class, 'getCitasPorMes']);
            Route::get('ingresos-por-mes/{timeRange?}', [EstadisticaController::class, 'getIngresosPorMes']);
            Route::get('especialidades/{timeRange?}', [EstadisticaController::class, 'getEspecialidadesMasSolicitadas']);
            Route::get('horarios-pico/{timeRange?}', [EstadisticaController::class, 'getHorariosPico']);
            Route::get('metricas-operativas', [EstadisticaController::class, 'getMetricasOperativas']);
            Route::get('rendimiento-financiero', [EstadisticaController::class, 'getRendimientoFinanciero']);
            Route::get('personal', [EstadisticaController::class, 'getPersonalStats']);
        });
        
        // Rutas de administración de base de datos
        Route::prefix('database')->group(function () {
            Route::get('stats', [DatabaseController::class, 'getStats']);
            Route::post('backup', [DatabaseController::class, 'createBackup']);
            Route::get('backups', [DatabaseController::class, 'getBackups']);
            Route::post('optimize', [DatabaseController::class, 'optimize']);
            Route::get('health', [DatabaseController::class, 'healthCheck']);
            Route::get('connections', [DatabaseController::class, 'getConnections']);
        });
        
        // Rutas de configuración del sistema
        Route::prefix('configuracion')->group(function () {
            Route::get('/', [ConfiguracionController::class, 'index']);
            Route::put('/', [ConfiguracionController::class, 'update']);
            Route::get('{category}', [ConfiguracionController::class, 'getByCategory']);
            Route::put('{category}', [ConfiguracionController::class, 'updateByCategory']);
            Route::post('reset/{category?}', [ConfiguracionController::class, 'reset']);
            Route::get('export', [ConfiguracionController::class, 'export']);
            Route::post('import', [ConfiguracionController::class, 'import']);
            Route::post('validate', [ConfiguracionController::class, 'validateConfig']);
            Route::get('history', [ConfiguracionController::class, 'getHistory']);
        });
        
        // Rutas de gestión de roles y usuarios (solo administradores)
        Route::prefix('role-management')->group(function () {
            Route::get('users', [RoleManagementController::class, 'getUsers']);
            Route::get('roles', [RoleManagementController::class, 'getRoles']);
            Route::put('users/{userId}/role', [RoleManagementController::class, 'changeUserRole']);
            Route::put('users/{userId}/status', [RoleManagementController::class, 'toggleUserStatus']);
            Route::get('stats', [RoleManagementController::class, 'getUserStats']);
            Route::get('history', [RoleManagementController::class, 'getRoleChangeHistory']);
        });
        
        // Rutas del dashboard administrativo
        Route::prefix('dashboard')->group(function () {
            Route::get('stats', [DashboardController::class, 'getStats']);
            Route::get('counts', [DashboardController::class, 'getBasicCounts']);
        });
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
    Route::post('pacientes/{pacienteId}/historial-medico', [PacienteController::class, 'crearHistorialMedico']);
    Route::put('pacientes/{pacienteId}/historial-medico', [\App\Http\Controllers\HistorialMedicoController::class, 'actualizarHistorialTerapeuta']);
    Route::post('pacientes/{pacienteId}/registros', [PacienteController::class, 'agregarRegistroHistorial']);
    Route::post('pacientes/{pacienteId}/notas', [PacienteController::class, 'agregarNotaPaciente']);
    Route::post('citas/{citaId}/completar', [\App\Http\Controllers\CitaController::class, 'completarCita']);
});

// Rutas específicas para recepcionistas (incluye administradores)
Route::prefix('recepcionista')->middleware(['auth:sanctum', 'role:1,3'])->group(function () {
    Route::get('citas-hoy', [CitaController::class, 'citasHoy']);
    Route::post('registrar-llegada/{cita}', [CitaController::class, 'registrarLlegada']);
    Route::get('citas', [CitaController::class, 'citasRecepcionista']);
    Route::post('citas/{citaId}/pago', [CitaController::class, 'procesarPago']);
    Route::get('tarifas', [CitaController::class, 'obtenerTarifas']);
});

// ==========================================
// RUTAS DE DESARROLLO Y TESTING
// ==========================================

// Debug específico para Railway (temporal)
Route::get('debug-railway-login', function () {
    try {
        $debug = [];
        
        // 1. Verificar conexión BD
        $debug['db_connection'] = 'OK';
        $debug['usuarios_count'] = \App\Models\Usuario::count();
        $debug['roles_count'] = \App\Models\Rol::count();
        
        // 2. Verificar terapeutas
        $terapeutas = \App\Models\Usuario::where('rol_id', 2)->with('rol')->get();
        $debug['terapeutas_count'] = $terapeutas->count();
        $debug['terapeutas'] = $terapeutas->map(function($t) {
            return [
                'id' => $t->id,
                'email' => $t->correo_electronico,
                'rol' => $t->rol->nombre ?? 'N/A'
            ];
        });
        
        // 3. Verificar tabla terapeutas
        try {
            $debug['tabla_terapeutas_count'] = \App\Models\Terapeuta::count();
        } catch (\Exception $e) {
            $debug['tabla_terapeutas_error'] = $e->getMessage();
        }
        
        // 4. Probar UserRoleRegistrationService
        $testUser = $terapeutas->first();
        if ($testUser) {
            try {
                $result = \App\Services\UserRoleRegistrationService::createRoleSpecificRecord($testUser);
                $debug['role_service_create'] = $result;
                
                $profileData = \App\Services\UserRoleRegistrationService::getUserProfileData($testUser);
                $debug['profile_data'] = [
                    'role_name' => $profileData['role_name'],
                    'profile_complete' => $profileData['profile_complete']
                ];
            } catch (\Exception $e) {
                $debug['role_service_error'] = $e->getMessage();
            }
        }
        
        // 5. Configuración
        $debug['config'] = [
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'db_connection' => config('database.default'),
            'timezone' => config('app.timezone')
        ];
        
        return response()->json($debug);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});


// Endpoint de prueba de login simplificado
Route::post('debug-simple-login', function (\Illuminate\Http\Request $request) {
    try {
        \Illuminate\Support\Facades\Log::info('Debug login attempt', $request->all());
        
        $email = $request->input('correo_electronico') ?? $request->input('email');
        $password = $request->input('contraseña') ?? $request->input('password');
        
        if (!$email || !$password) {
            return response()->json(['error' => 'Email y contraseña requeridos'], 400);
        }
        
        $usuario = \App\Models\Usuario::where('correo_electronico', $email)->first();
        
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
        
        if (!\Illuminate\Support\Facades\Hash::check($password, $usuario->contraseña)) {
            return response()->json(['error' => 'Contraseña incorrecta'], 401);
        }
        
        // Login exitoso - crear token
        $token = $usuario->createToken('Debug API Token')->plainTextToken;
        
        return response()->json([
            'success' => true,
            'usuario' => $usuario,
            'token' => $token,
            'message' => 'Login exitoso'
        ]);
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Debug login error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Test específico para UserRoleRegistrationService
Route::get('debug-user-role-service/{userId}', function ($userId) {
    try {
        $usuario = \App\Models\Usuario::find($userId);
        
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
        
        $result = [];
        $result['usuario'] = [
            'id' => $usuario->id,
            'email' => $usuario->correo_electronico,
            'rol_id' => $usuario->rol_id
        ];
        
        // Test createRoleSpecificRecord
        try {
            $createResult = \App\Services\UserRoleRegistrationService::createRoleSpecificRecord($usuario);
            $result['createRoleSpecificRecord'] = $createResult;
        } catch (\Exception $e) {
            $result['createRoleSpecificRecord_error'] = $e->getMessage();
        }
        
        // Test getUserProfileData
        try {
            $profileData = \App\Services\UserRoleRegistrationService::getUserProfileData($usuario);
            $result['getUserProfileData'] = [
                'role_name' => $profileData['role_name'],
                'profile_complete' => $profileData['profile_complete'],
                'missing_fields_count' => count($profileData['missing_fields'])
            ];
        } catch (\Exception $e) {
            $result['getUserProfileData_error'] = $e->getMessage();
        }
        
        // Verificar relaciones específicas
        switch ($usuario->rol_id) {
            case 2: // Terapeuta
                try {
                    $terapeuta = \App\Models\Terapeuta::find($usuario->id);
                    $result['terapeuta_record'] = $terapeuta ? 'exists' : 'missing';
                } catch (\Exception $e) {
                    $result['terapeuta_error'] = $e->getMessage();
                }
                break;
            case 4: // Paciente
                try {
                    $paciente = \App\Models\Paciente::find($usuario->id);
                    $result['paciente_record'] = $paciente ? 'exists' : 'missing';
                } catch (\Exception $e) {
                    $result['paciente_error'] = $e->getMessage();
                }
                break;
        }
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Endpoint para ver logs recientes (temporal para Railway)
Route::get('debug-logs', function () {
    try {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return response()->json(['error' => 'Log file not found']);
        }
        
        // Leer las últimas 100 líneas del log
        $lines = [];
        $file = new SplFileObject($logFile, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $linesToRead = min(100, $totalLines);
        $startLine = max(0, $totalLines - $linesToRead);
        
        $file->seek($startLine);
        while (!$file->eof()) {
            $line = $file->current();
            if (trim($line)) {
                $lines[] = $line;
            }
            $file->next();
        }
        
        return response()->json([
            'total_lines' => $totalLines,
            'showing_last' => count($lines),
            'logs' => $lines
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

if (app()->environment(['local', 'testing'])) {
    
    // Rutas básicas de prueba
    Route::get('prueba', function () {
        return response()->json(['mensaje' => 'API funcionando correctamente']);
    });

    // Debug headers y autenticación
    Route::get('debug-headers', function (Illuminate\Http\Request $request) {
        return response()->json([
            'headers' => $request->headers->all(),
            'auth_header' => $request->header('Authorization'),
            'bearer_token' => $request->bearerToken(),
            'user' => $request->user(),
            'message' => 'Debug endpoint'
        ]);
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

        Route::get('debug-paciente/{id}', function ($id) {
            try {
                $paciente = \App\Models\Paciente::with([
                    'usuario',
                    'historialMedico.registros' => function ($query) {
                        $query->orderBy('Fecha_Hora', 'desc');
                    }
                ])->find($id);

                if (!$paciente) {
                    return response()->json(['error' => 'Paciente no encontrado'], 404);
                }

                return response()->json([
                    'paciente_raw' => $paciente->toArray(),
                    'historial_exists' => $paciente->historialMedico ? true : false,
                    'registros_count' => $paciente->historialMedico?->registros?->count() ?? 0,
                    'registros_data' => $paciente->historialMedico?->registros?->toArray() ?? []
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Error: ' . $e->getMessage()
                ], 500);
            }
        });
    });
}
