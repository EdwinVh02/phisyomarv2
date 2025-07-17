<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AdministradorController;
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
use App\Http\Controllers\TerapeutaController;
use App\Http\Controllers\TratamientoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ValoracionController;

// AUTH - Sistema Sanctum (original)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('user', [AuthController::class, 'user']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// AUTH - Sistema Simple (alternativo)
Route::post('simple-login', [\App\Http\Controllers\Api\SimpleAuthController::class, 'login']);
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('pacientes', PacienteController::class);
Route::apiResource('citas', CitaController::class);
Route::apiResource('terapeutas', TerapeutaController::class);
Route::apiResource('administradores', AdministradorController::class);
Route::apiResource('clinicas', ClinicaController::class);
Route::apiResource('bitacoras', BitacoraController::class);
Route::apiResource('recepcionistas', RecepcionistaController::class);
Route::apiResource('encuestas', EncuestaController::class);
Route::apiResource('pagos', PagoController::class);
Route::apiResource('registros', RegistroController::class);
Route::apiResource('historiales', HistorialMedicoController::class);
Route::apiResource('consentimientos', ConsentimientoInformadoController::class);
Route::apiResource('valoraciones', ValoracionController::class);
Route::apiResource('smartwatches', SmartwatchController::class);

// Rutas de solo lectura para todos
Route::apiResource('especialidades', EspecialidadController::class)->only(['index', 'show']);
Route::apiResource('padecimientos', PadecimientoController::class)->only(['index', 'show']);
Route::apiResource('tarifas', TarifaController::class)->only(['index', 'show']);
Route::apiResource('tratamientos', TratamientoController::class)->only(['index', 'show']);
Route::apiResource('preguntas', PreguntaController::class);
Route::apiResource('respuestas', RespuestaController::class);

// RUTAS ESPECÍFICAS PARA PACIENTES
Route::prefix('paciente')->middleware('auth:sanctum')->group(function () {
    Route::get('mis-citas', [CitaController::class, 'misCitas']);
    Route::get('mi-historial', [HistorialMedicoController::class, 'miHistorial']);
    Route::post('agendar-cita', [CitaController::class, 'agendarCita']);
    Route::put('cancelar-cita/{id}', [CitaController::class, 'cancelarCita']);
});

// RUTAS PARA DISPONIBILIDAD DE CITAS
Route::prefix('citas')->group(function () {
    Route::post('calendario-disponibilidad', [CitaController::class, 'calendarioDisponibilidad']);
    Route::post('horas-disponibles', [CitaController::class, 'horasDisponibles']);
    Route::post('fechas-disponibles', [CitaController::class, 'fechasDisponibles']);
});

// RUTAS ESPECÍFICAS PARA TERAPEUTAS
Route::prefix('terapeuta')->middleware('auth:sanctum')->group(function () {
    Route::get('mis-citas', [CitaController::class, 'misCitasTerapeuta']);
    Route::get('mis-pacientes', [PacienteController::class, 'misPacientes']);
    Route::get('estadisticas', [RegistroController::class, 'estadisticasTerapeuta']);
});

Route::get('prueba', function () {
    return 'ok';
});

// Ruta de prueba para citas sin autenticación
Route::get('test-citas', function () {
    return response()->json(\App\Models\Cita::all());
});

// Ruta de prueba para crear cita directamente
Route::post('test-create-cita', function (\Illuminate\Http\Request $request) {
    try {
        $cita = \App\Models\Cita::create([
            'fecha_hora' => now()->addDays(1),
            'tipo' => 'Consulta',
            'duracion' => 60,
            'motivo' => 'Prueba de inserción',
            'estado' => 'agendada',
            'paciente_id' => 1, // Asegúrate de que existe un paciente con ID 1
            'terapeuta_id' => 1, // Asegúrate de que existe un terapeuta con ID 1
        ]);
        
        return response()->json([
            'success' => true, 
            'cita' => $cita,
            'message' => 'Cita creada exitosamente en test'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Ruta de prueba para agendar cita sin middleware (para debugging)
Route::post('test-agendar-cita', [CitaController::class, 'agendarCita']);

// Ruta de prueba para calendario sin middleware
Route::post('test-calendario-disponibilidad', [CitaController::class, 'calendarioDisponibilidad']);

// Rutas de prueba sin middleware para debugging
Route::get('test-usuarios', function () {
    return \App\Models\Usuario::all();
});

Route::get('test-pacientes', function () {
    return \App\Models\Paciente::with('usuario')->get();
});

Route::get('test-terapeutas', function () {
    return \App\Models\Terapeuta::with('usuario')->get();
});

// Ruta especial para obtener terapeutas para el formulario (sin autenticación)
Route::get('terapeutas-publico', function () {
    try {
        $terapeutas = \App\Models\Terapeuta::with('usuario')->get()->map(function ($terapeuta) {
            return [
                'id' => $terapeuta->id,
                'numero_cedula' => $terapeuta->numero_cedula,
                'telefono_consultorio' => $terapeuta->telefono_consultorio,
                'especialidad_principal' => 'Fisioterapia', // Valor por defecto
                'usuario' => [
                    'nombre' => $terapeuta->usuario->nombre ?? 'Sin nombre',
                    'apellido_paterno' => $terapeuta->usuario->apellido_paterno ?? '',
                    'apellido_materno' => $terapeuta->usuario->apellido_materno ?? '',
                ]
            ];
        });
        
        return response()->json($terapeutas);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

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

// Crear datos de prueba mínimos
Route::post('create-test-data', function () {
    try {
        // Crear roles si no existen
        if (!\App\Models\Rol::where('id', 2)->exists()) {
            \App\Models\Rol::create(['id' => 2, 'nombre' => 'Terapeuta', 'descripcion' => 'Fisioterapeuta']);
        }
        if (!\App\Models\Rol::where('id', 4)->exists()) {
            \App\Models\Rol::create(['id' => 4, 'nombre' => 'Paciente', 'descripcion' => 'Paciente del sistema']);
        }
        // Crear paciente de prueba
        $usuario = \App\Models\Usuario::create([
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'García',
            'correo_electronico' => 'paciente@test.com',
            'contraseña' => 'password123',
            'telefono' => '5551234567',
            'direccion' => 'Calle Test 123',
            'fecha_nacimiento' => '1990-01-01',
            'sexo' => 'M',
            'curp' => 'PEGJ900101HDFRNN01',
            'ocupacion' => 'Ingeniero',
            'estatus' => 'activo',
            'rol_id' => 4 // Paciente
        ]);

        $paciente = \App\Models\Paciente::create([
            'id' => $usuario->id,
            'contacto_emergencia_nombre' => 'María Pérez',
            'contacto_emergencia_telefono' => '5559876543',
            'contacto_emergencia_parentesco' => 'Esposa',
        ]);

        // Crear terapeuta de prueba
        $usuarioTerapeuta = \App\Models\Usuario::create([
            'nombre' => 'Dr. Carlos',
            'apellido_paterno' => 'Rodríguez',
            'apellido_materno' => 'López',
            'correo_electronico' => 'terapeuta@test.com',
            'contraseña' => 'password123',
            'telefono' => '5551234568',
            'direccion' => 'Calle Terapia 456',
            'fecha_nacimiento' => '1985-01-01',
            'sexo' => 'M',
            'curp' => 'ROLC850101HDFRNN02',
            'ocupacion' => 'Fisioterapeuta',
            'estatus' => 'activo',
            'rol_id' => 2 // Terapeuta
        ]);

        $terapeuta = \App\Models\Terapeuta::create([
            'id' => $usuarioTerapeuta->id,
            'numero_cedula' => '12345678',
            'telefono_consultorio' => '5551234569',
        ]);

        return response()->json([
            'success' => true,
            'paciente' => $paciente->load('usuario'),
            'terapeuta' => $terapeuta->load('usuario'),
            'message' => 'Datos de prueba creados'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Crear terapeutas de prueba con SQL directo
Route::post('create-terapeutas', function () {
    try {
        // Crear roles si no existen
        \DB::statement("
            INSERT IGNORE INTO rols (id, nombre, descripcion, created_at, updated_at)
            VALUES 
            (2, 'Terapeuta', 'Fisioterapeuta profesional', NOW(), NOW()),
            (4, 'Paciente', 'Paciente del sistema', NOW(), NOW())
        ");

        // Usar SQL directo para evitar problemas de hash
        \DB::statement("
            INSERT IGNORE INTO usuarios (id, nombre, apellido_paterno, apellido_materno, correo_electronico, contraseña, telefono, direccion, fecha_nacimiento, sexo, curp, ocupacion, estatus, rol_id, created_at, updated_at)
            VALUES 
            (10, 'Dr. Juan', 'Terapeuta', 'Uno', 'terapeuta1@phisyomar.com', ?, '5551111111', 'Dirección 1', '1990-03-10', 'M', 'JUAT900310HDFXXX01', 'Fisioterapeuta', 'activo', 2, NOW(), NOW()),
            (11, 'Dra. Ana', 'Terapeuta', 'Dos', 'terapeuta2@phisyomar.com', ?, '5552222222', 'Dirección 2', '1992-07-22', 'F', 'AANT920722MDFXXX01', 'Fisioterapeuta', 'activo', 2, NOW(), NOW())
        ", [bcrypt('terapeuta123'), bcrypt('terapeuta123')]);

        \DB::statement("
            INSERT IGNORE INTO terapeutas (id, numero_cedula, telefono_consultorio, created_at, updated_at)
            VALUES 
            (10, '1234567890', '5551111112', NOW(), NOW()),
            (11, '0987654321', '5552222223', NOW(), NOW())
        ");

        return response()->json([
            'success' => true,
            'message' => 'Terapeutas creados exitosamente',
            'terapeutas' => \App\Models\Terapeuta::with('usuario')->get()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Crear paciente de prueba con SQL directo
Route::post('create-paciente', function () {
    try {
        \DB::statement("
            INSERT IGNORE INTO usuarios (id, nombre, apellido_paterno, apellido_materno, correo_electronico, contraseña, telefono, direccion, fecha_nacimiento, sexo, curp, ocupacion, estatus, rol_id, created_at, updated_at)
            VALUES 
            (20, 'María', 'Paciente', 'Test', 'paciente1@phisyomar.com', ?, '5553333333', 'Dirección Paciente', '1995-05-15', 'F', 'MAPT950515MDFXXX01', 'Estudiante', 'activo', 4, NOW(), NOW())
        ", [bcrypt('paciente123')]);

        \DB::statement("
            INSERT IGNORE INTO pacientes (id, contacto_emergencia_nombre, contacto_emergencia_telefono, contacto_emergencia_parentesco, created_at, updated_at)
            VALUES 
            (20, 'José Paciente', '5554444444', 'Esposo', NOW(), NOW())
        ");

        return response()->json([
            'success' => true,
            'message' => 'Paciente creado exitosamente',
            'paciente' => \App\Models\Paciente::with('usuario')->find(20)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Crear todos los datos de prueba de una vez
Route::post('setup-test-data', function () {
    try {
        // 1. Verificar si la tabla roles o rols existe
        $tablaRoles = 'rols';
        try {
            \DB::select("SELECT 1 FROM rols LIMIT 1");
        } catch (\Exception $e) {
            try {
                \DB::select("SELECT 1 FROM roles LIMIT 1");
                $tablaRoles = 'roles';
            } catch (\Exception $e2) {
                // Crear tabla rols si no existe
                \DB::statement("
                    CREATE TABLE IF NOT EXISTS rols (
                        id bigint unsigned not null primary key,
                        nombre varchar(50) not null,
                        descripcion text,
                        created_at timestamp null,
                        updated_at timestamp null
                    )
                ");
            }
        }

        // Crear roles
        \DB::statement("
            INSERT IGNORE INTO {$tablaRoles} (id, nombre, descripcion, created_at, updated_at)
            VALUES 
            (2, 'Terapeuta', 'Fisioterapeuta profesional', NOW(), NOW()),
            (4, 'Paciente', 'Paciente del sistema', NOW(), NOW())
        ");

        // 2. Crear usuarios y terapeutas
        \DB::statement("
            INSERT IGNORE INTO usuarios (id, nombre, apellido_paterno, apellido_materno, correo_electronico, contraseña, telefono, direccion, fecha_nacimiento, sexo, curp, ocupacion, estatus, rol_id, created_at, updated_at)
            VALUES 
            (10, 'Dr. Juan', 'Terapeuta', 'Uno', 'terapeuta1@phisyomar.com', ?, '5551111111', 'Dirección 1', '1990-03-10', 'M', 'JUAT900310HDFXXX01', 'Fisioterapeuta', 'activo', 2, NOW(), NOW())
        ", [bcrypt('terapeuta123')]);

        \DB::statement("
            INSERT IGNORE INTO terapeutas (id, numero_cedula, telefono_consultorio, created_at, updated_at)
            VALUES 
            (10, '1234567890', '5551111112', NOW(), NOW())
        ");

        // 3. Crear usuario y paciente
        \DB::statement("
            INSERT IGNORE INTO usuarios (id, nombre, apellido_paterno, apellido_materno, correo_electronico, contraseña, telefono, direccion, fecha_nacimiento, sexo, curp, ocupacion, estatus, rol_id, created_at, updated_at)
            VALUES 
            (20, 'María', 'Paciente', 'Test', 'paciente1@phisyomar.com', ?, '5553333333', 'Dirección Paciente', '1995-05-15', 'F', 'MAPT950515MDFXXX01', 'Estudiante', 'activo', 4, NOW(), NOW())
        ", [bcrypt('paciente123')]);

        \DB::statement("
            INSERT IGNORE INTO pacientes (id, contacto_emergencia_nombre, contacto_emergencia_telefono, contacto_emergencia_parentesco, created_at, updated_at)
            VALUES 
            (20, 'José Paciente', '5554444444', 'Esposo', NOW(), NOW())
        ");

        return response()->json([
            'success' => true,
            'message' => 'Todos los datos de prueba creados exitosamente',
            'data' => [
                'terapeutas' => \App\Models\Terapeuta::with('usuario')->get(),
                'pacientes' => \App\Models\Paciente::with('usuario')->get(),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Ruta temporal para login directo sin middleware
Route::post('temp-login', function (\Illuminate\Http\Request $request) {
    $correo = $request->input('correo_electronico');
    $password = $request->input('contraseña');
    
    $usuario = \App\Models\Usuario::where('correo_electronico', $correo)->first();
    
    if (!$usuario) {
        return response()->json(['error' => 'Usuario no encontrado'], 404);
    }
    
    // Verificar contraseña
    if (!\Illuminate\Support\Facades\Hash::check($password, $usuario->contraseña)) {
        return response()->json(['error' => 'Contraseña incorrecta'], 401);
    }
    
    // Crear token si tiene Sanctum
    $token = $usuario->createToken('API Token')->plainTextToken;
    
    return response()->json([
        'success' => true,
        'usuario' => $usuario,
        'token' => $token,
        'message' => 'Login exitoso'
    ]);
});

// Ruta simple para crear solo terapeutas directamente
Route::post('simple-create-data', function () {
    try {
        // Insertar terapeuta directamente
        \DB::statement("
            INSERT IGNORE INTO usuarios (id, nombre, apellido_paterno, apellido_materno, correo_electronico, contraseña, telefono, direccion, fecha_nacimiento, sexo, curp, ocupacion, estatus, rol_id, created_at, updated_at)
            VALUES 
            (100, 'Dr. Juan', 'González', 'Pérez', 'doctor@test.com', ?, '5551111111', 'Consultorio 1', '1985-01-01', 'M', 'GOPE850101HDFXXX01', 'Fisioterapeuta', 'activo', 2, NOW(), NOW())
        ", [password_hash('password123', PASSWORD_DEFAULT)]);

        \DB::statement("
            INSERT IGNORE INTO terapeutas (id, cedula_profesional, especialidad_principal, experiencia_anios, estatus, created_at, updated_at)
            VALUES 
            (100, '1234567890', 'Fisioterapia', 5, 'activo', NOW(), NOW())
        ");

        // También crear un paciente para las pruebas
        \DB::statement("
            INSERT IGNORE INTO usuarios (id, nombre, apellido_paterno, apellido_materno, correo_electronico, contraseña, telefono, direccion, fecha_nacimiento, sexo, curp, ocupacion, estatus, rol_id, created_at, updated_at)
            VALUES 
            (200, 'María', 'López', 'Hernández', 'paciente@test.com', ?, '5552222222', 'Casa Paciente', '1990-05-15', 'F', 'LOHM900515MDFXXX01', 'Estudiante', 'activo', 4, NOW(), NOW())
        ", [password_hash('password123', PASSWORD_DEFAULT)]);

        \DB::statement("
            INSERT IGNORE INTO pacientes (id, contacto_emergencia_nombre, contacto_emergencia_telefono, contacto_emergencia_parentesco, created_at, updated_at)
            VALUES 
            (200, 'José López', '5553333333', 'Esposo', NOW(), NOW())
        ");

        return response()->json([
            'success' => true,
            'message' => 'Terapeuta y paciente creados exitosamente',
            'data' => [
                'terapeuta' => ['id' => 100, 'nombre' => 'Dr. Juan González Pérez'],
                'paciente' => ['id' => 200, 'nombre' => 'María López Hernández']
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
});

// Probar inserción directa de cita
Route::post('test-direct-cita', function (\Illuminate\Http\Request $request) {
    try {
        // Verificar que existen los datos necesarios
        $paciente = \App\Models\Paciente::find(200);
        $terapeuta = \App\Models\Terapeuta::find(100);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente 200 no existe'], 404);
        }
        if (!$terapeuta) {
            return response()->json(['error' => 'Terapeuta 100 no existe'], 404);
        }

        // Crear cita con datos mínimos
        $cita = \App\Models\Cita::create([
            'fecha_hora' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'tipo' => 'Consulta',
            'duracion' => 60,
            'motivo' => 'Consulta de prueba',
            'estado' => 'agendada',
            'paciente_id' => 200,
            'terapeuta_id' => 100,
        ]);

        return response()->json([
            'success' => true,
            'cita' => $cita,
            'paciente' => $paciente->load('usuario'),
            'terapeuta' => $terapeuta->load('usuario'),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile()),
            'trace' => explode("\n", $e->getTraceAsString())[0]
        ], 500);
    }
});

// Ruta temporal para obtener citas sin autenticación (para debug)
Route::get('test-mis-citas', function () {
    try {
        // Obtener todas las citas para el paciente de prueba (ID 200)
        $citas = \App\Models\Cita::where('paciente_id', 200)
                    ->with(['terapeuta.usuario', 'paciente.usuario'])
                    ->orderBy('fecha_hora', 'desc')
                    ->get()
                    ->map(function($cita) {
                        return [
                            'id' => $cita->id,
                            'fecha_hora' => $cita->fecha_hora,
                            'tipo' => $cita->tipo,
                            'duracion' => $cita->duracion,
                            'motivo' => $cita->motivo,
                            'estado' => $cita->estado,
                            'ubicacion' => $cita->ubicacion,
                            'observaciones' => $cita->observaciones,
                            'terapeuta' => [
                                'id' => $cita->terapeuta->id ?? null,
                                'nombre' => $cita->terapeuta->usuario->nombre ?? null,
                                'apellido_paterno' => $cita->terapeuta->usuario->apellido_paterno ?? null,
                                'especialidad' => $cita->terapeuta->especialidad_principal ?? null,
                            ],
                            'fecha' => $cita->fecha_hora ? \Carbon\Carbon::parse($cita->fecha_hora)->format('Y-m-d') : null,
                            'hora' => $cita->fecha_hora ? \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i') : null,
                        ];
                    });

        return response()->json($citas);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile())
        ], 500);
    }
});

// Ruta para cancelar cita sin autenticación (para debug)
Route::put('test-cancelar-cita/{id}', function ($id) {
    try {
        $cita = \App\Models\Cita::find($id);
        
        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        if ($cita->estado === 'cancelada') {
            return response()->json(['error' => 'La cita ya está cancelada'], 400);
        }

        $cita->update(['estado' => 'cancelada']);

        return response()->json([
            'message' => 'Cita cancelada exitosamente',
            'cita' => $cita
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile())
        ], 500);
    }
});

// Ruta para obtener detalle de una cita específica
Route::get('test-cita-detalle/{id}', function ($id) {
    try {
        $cita = \App\Models\Cita::with(['terapeuta.usuario', 'paciente.usuario'])
                    ->find($id);
        
        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        $detalle = [
            'id' => $cita->id,
            'fecha_hora' => $cita->fecha_hora,
            'tipo' => $cita->tipo,
            'duracion' => $cita->duracion,
            'motivo' => $cita->motivo,
            'estado' => $cita->estado,
            'ubicacion' => $cita->ubicacion,
            'equipo_asignado' => $cita->equipo_asignado,
            'observaciones' => $cita->observaciones,
            'escala_dolor_eva_inicio' => $cita->escala_dolor_eva_inicio,
            'escala_dolor_eva_fin' => $cita->escala_dolor_eva_fin,
            'como_fue_lesion' => $cita->como_fue_lesion,
            'antecedentes_patologicos' => $cita->antecedentes_patologicos,
            'antecedentes_no_patologicos' => $cita->antecedentes_no_patologicos,
            'terapeuta' => [
                'id' => $cita->terapeuta->id ?? null,
                'nombre' => $cita->terapeuta->usuario->nombre ?? null,
                'apellido_paterno' => $cita->terapeuta->usuario->apellido_paterno ?? null,
                'apellido_materno' => $cita->terapeuta->usuario->apellido_materno ?? null,
                'especialidad' => $cita->terapeuta->especialidad_principal ?? null,
                'cedula' => $cita->terapeuta->cedula_profesional ?? null,
                'telefono' => $cita->terapeuta->usuario->telefono ?? null,
            ],
            'paciente' => [
                'id' => $cita->paciente->id ?? null,
                'nombre' => $cita->paciente->usuario->nombre ?? null,
                'apellido_paterno' => $cita->paciente->usuario->apellido_paterno ?? null,
                'apellido_materno' => $cita->paciente->usuario->apellido_materno ?? null,
                'telefono' => $cita->paciente->usuario->telefono ?? null,
                'contacto_emergencia' => $cita->paciente->contacto_emergencia_nombre ?? null,
            ],
            'fecha' => $cita->fecha_hora ? \Carbon\Carbon::parse($cita->fecha_hora)->format('Y-m-d') : null,
            'hora' => $cita->fecha_hora ? \Carbon\Carbon::parse($cita->fecha_hora)->format('H:i') : null,
        ];

        return response()->json($detalle);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => basename($e->getFile())
        ], 500);
    }
});

// Ruta de prueba con middleware completo
Route::middleware(['auth:sanctum', 'role:1,3'])->get('test-pacientes-protected', function () {
    return \App\Models\Paciente::with('usuario')->get();
});

// Ruta que hace login y devuelve pacientes en un paso
Route::post('login-and-patients', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'correo_electronico' => 'required|email',
        'contraseña' => 'required',
    ]);

    $usuario = \App\Models\Usuario::where('correo_electronico', $request->correo_electronico)->first();

    if (!$usuario || !\Illuminate\Support\Facades\Hash::check($request->contraseña, $usuario->contraseña)) {
        return response()->json(['error' => 'Credenciales incorrectas'], 401);
    }

    // Verificar rol (admin o recepcionista pueden ver pacientes)
    if (!in_array($usuario->rol_id, [1, 3])) {
        return response()->json(['error' => 'Sin permisos para ver pacientes'], 403);
    }

    $token = $usuario->createToken('API Token')->plainTextToken;
    $pacientes = \App\Models\Paciente::with('usuario')->get();

    return response()->json([
        'usuario' => $usuario,
        'token' => $token,
        'pacientes' => $pacientes
    ]);
});

// Rutas de prueba adicionales (pueden eliminarse en producción)
Route::get('test-usuarios', function () {
    return \App\Models\Usuario::all();
});

Route::get('test-pacientes', function () {
    return \App\Models\Paciente::with('usuario')->get();
});
