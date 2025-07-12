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

// AUTH
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);

    Route::apiResource('administradores', AdministradorController::class);
    Route::apiResource('bitacoras', BitacoraController::class);
    Route::apiResource('citas', CitaController::class);
    Route::apiResource('clinicas', ClinicaController::class);
    Route::apiResource('consentimientos', ConsentimientoInformadoController::class);
    Route::apiResource('encuestas', EncuestaController::class);
    Route::apiResource('especialidades', EspecialidadController::class);
    Route::apiResource('historiales', HistorialMedicoController::class);
    Route::apiResource('pacientes', PacienteController::class);
    Route::apiResource('padecimientos', PadecimientoController::class);
    Route::apiResource('pagos', PagoController::class);
    Route::apiResource('paquetes_paciente', PaquetePacienteController::class);
    Route::apiResource('paquetes_sesion', PaqueteSesionController::class);
    Route::apiResource('preguntas', PreguntaController::class);
    Route::apiResource('recepcionistas', RecepcionistaController::class);
    Route::apiResource('registros', RegistroController::class);
    Route::apiResource('respuestas', RespuestaController::class);
    Route::apiResource('smartwatches', SmartwatchController::class);
    Route::apiResource('tarifas', TarifaController::class);
    Route::apiResource('terapeutas', TerapeutaController::class);
    Route::apiResource('tratamientos', TratamientoController::class);
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('valoraciones', ValoracionController::class);
});
