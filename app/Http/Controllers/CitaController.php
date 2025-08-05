<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\UpdateCitaRequest;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CitaController extends Controller
{
    public function __construct()
    {
        // Sin middleware global - se aplicará en las rutas
    }

    public function index()
    {
        $citas = Cita::with([
                'paciente.usuario', 
                'terapeuta.usuario', 
                'pagoActual',
                'paquetePaciente'
            ])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $citas
        ], 200);
    }

    public function store(StoreCitaRequest $request)
    {
        $data = $request->validated();

        $cita = Cita::create($data);

        // Crear o actualizar historial médico automáticamente
        if ($cita->paciente_id) {
            $historialController = new \App\Http\Controllers\HistorialMedicoController();
            $historialController->crearOActualizarDesdeCita($cita);
        }

        return response()->json($cita, 201);
    }

    public function show(Cita $cita)
    {
        return response()->json($cita, 200);
    }

    public function update(UpdateCitaRequest $request, Cita $cita)
    {
        $data = $request->validated();
        $cita->update($data);

        return response()->json($cita, 200);
    }

    public function destroy(Cita $cita)
    {
        $cita->delete();

        return response()->json(['mensaje' => 'Cita eliminada exitosamente'], 200);
    }

    // Métodos específicos para pacientes
    public function misCitas(Request $request)
    {
        try {
            $user = $request->user();

            // Verificar que el usuario sea un paciente
            if ($user->rol_id !== 4) {
                return response()->json(['error' => 'No autorizado. Solo los pacientes pueden ver sus citas.'], 403);
            }

            // El middleware ya se encarga de crear el registro de paciente automáticamente

            $citas = Cita::where('paciente_id', $user->id)
                ->with(['terapeuta.usuario', 'registro'])
                ->orderBy('fecha_hora', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $citas,
                'message' => 'Citas obtenidas correctamente'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error en misCitas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function miCitaDetalle(Request $request, $id)
    {
        $user = $request->user();

        // Verificar que el usuario sea un paciente
        if ($user->rol_id !== 4) {
            return response()->json(['error' => 'No autorizado. Solo los pacientes pueden ver sus citas.'], 403);
        }

        $cita = Cita::where('id', $id)
            ->where('paciente_id', $user->id)
            ->with(['terapeuta.usuario', 'registro'])
            ->first();

        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        return response()->json($cita, 200);
    }

    public function agendarCita(Request $request)
    {
        $user = $request->user();

        // Si no hay usuario (modo de prueba), usar paciente ID 200
        if (! $user) {
            $pacienteId = 200; // ID del paciente de prueba creado
            Log::info('Modo de prueba - usando paciente ID: ' . $pacienteId);
        } else {
            // Verificar que el usuario sea un paciente
            if ($user->rol_id !== 4) {
                return response()->json(['error' => 'No autorizado. Solo los pacientes pueden agendar citas.'], 403);
            }

            // El middleware ya se encarga de crear el registro de paciente automáticamente
            $pacienteId = $user->id;
        }

        // Si no se proporciona terapeuta_id en modo de prueba, usar ID 100
        if (! $user && ! $request->has('terapeuta_id')) {
            $request->merge(['terapeuta_id' => 100]); // ID del terapeuta de prueba
        }

        $validatedData = $request->validate([
            'fecha_hora' => [
                'required',
                'date',
                'after:now',
                new \App\Rules\CitaDisponible(
                    $request->input('terapeuta_id'),
                    $request->input('duracion', 60)
                ),
            ],
            'tipo' => 'required|string|max:50',
            'duracion' => 'nullable|integer|min:15|max:240',
            'ubicacion' => 'nullable|string|max:100',
            'equipo_asignado' => 'nullable|string|max:100',
            'motivo' => 'required|string',
            'terapeuta_id' => 'required|exists:terapeutas,id',
            'observaciones' => 'nullable|string',
            'escala_dolor_eva_inicio' => 'nullable|integer|min:0|max:10',
            'como_fue_lesion' => 'nullable|string',
            'antecedentes_patologicos' => 'nullable|string',
            'antecedentes_no_patologicos' => 'nullable|string',
        ], [
            'fecha_hora.required' => 'La fecha y hora es requerida.',
            'fecha_hora.date' => 'La fecha y hora debe ser una fecha válida.',
            'fecha_hora.after' => 'La fecha y hora debe ser futura.',
            'tipo.required' => 'El tipo de cita es requerido.',
            'motivo.required' => 'El motivo de la cita es requerido.',
            'terapeuta_id.required' => 'El terapeuta es requerido.',
            'terapeuta_id.exists' => 'El terapeuta seleccionado no existe.',
            'duracion.integer' => 'La duración debe ser un número entero.',
            'duracion.min' => 'La duración mínima es de 15 minutos.',
            'duracion.max' => 'La duración máxima es de 240 minutos.',
        ]);

        try {
            // Log para debugging
            Log::info('Intentando crear cita', [
                'paciente_id' => $pacienteId,
                'terapeuta_id' => $validatedData['terapeuta_id'],
                'fecha_hora' => $validatedData['fecha_hora'],
                'tipo' => $validatedData['tipo'],
                'motivo' => $validatedData['motivo'],
            ]);

            // Crear cita solo con campos básicos obligatorios
            $cita = Cita::create([
                'fecha_hora' => $validatedData['fecha_hora'],
                'tipo' => $validatedData['tipo'],
                'duracion' => $validatedData['duracion'] ?? 60,
                'motivo' => $validatedData['motivo'],
                'estado' => 'agendada',
                'paciente_id' => $pacienteId,
                'terapeuta_id' => $validatedData['terapeuta_id'],
                // Solo agregar campos opcionales si tienen valor
                'ubicacion' => ! empty($validatedData['ubicacion']) ? $validatedData['ubicacion'] : null,
                'equipo_asignado' => ! empty($validatedData['equipo_asignado']) ? $validatedData['equipo_asignado'] : null,
                'observaciones' => ! empty($validatedData['observaciones']) ? $validatedData['observaciones'] : null,
                'escala_dolor_eva_inicio' => ! empty($validatedData['escala_dolor_eva_inicio']) ? (int) $validatedData['escala_dolor_eva_inicio'] : null,
                'como_fue_lesion' => ! empty($validatedData['como_fue_lesion']) ? $validatedData['como_fue_lesion'] : null,
                'antecedentes_patologicos' => ! empty($validatedData['antecedentes_patologicos']) ? $validatedData['antecedentes_patologicos'] : null,
                'antecedentes_no_patologicos' => ! empty($validatedData['antecedentes_no_patologicos']) ? $validatedData['antecedentes_no_patologicos'] : null,
            ]);

            // Crear o actualizar historial médico automáticamente
            $historialController = new \App\Http\Controllers\HistorialMedicoController();
            $historialController->crearOActualizarDesdeCita($cita);

            return response()->json([
                'mensaje' => 'Cita agendada exitosamente',
                'cita' => $cita->load(['terapeuta.usuario']),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear cita: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al agendar la cita',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancelarCita(Request $request, $id)
    {
        $user = $request->user();

        // Verificar que el usuario sea un paciente
        if ($user->rol_id !== 4) {
            return response()->json(['error' => 'No autorizado. Solo los pacientes pueden cancelar sus citas.'], 403);
        }

        $cita = Cita::where('id', $id)
            ->where('paciente_id', $user->id)
            ->first();

        if (! $cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        if ($cita->estado === 'cancelada') {
            return response()->json(['error' => 'La cita ya está cancelada'], 400);
        }

        $cita->update(['estado' => 'cancelada']);

        return response()->json(['mensaje' => 'Cita cancelada exitosamente', 'cita' => $cita], 200);
    }

    // Métodos específicos para terapeutas
    public function misCitasTerapeuta(Request $request)
    {
        $user = $request->user();

        // Verificar que el usuario sea un terapeuta
        if ($user->rol_id !== 2) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $citas = Cita::where('terapeuta_id', $user->id)
            ->with(['paciente.usuario', 'registro'])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        return response()->json($citas, 200);
    }

    /**
     * Verificar si una fecha y hora está disponible para un terapeuta
     */
    public function verificarDisponibilidad(Request $request)
    {
        $request->validate([
            'fecha_hora' => 'required|date',
            'terapeuta_id' => 'required|exists:terapeutas,id',
            'duracion' => 'nullable|integer|min:15|max:240',
            'cita_id' => 'nullable|exists:citas,id',
        ]);

        $fechaHora = Carbon::parse($request->fecha_hora);
        $duracion = $request->duracion ?? 60;
        $disponible = Cita::estaDisponible(
            $request->fecha_hora,
            $request->terapeuta_id,
            $duracion,
            $request->cita_id
        );

        $response = [
            'disponible' => $disponible,
            'fecha_hora' => $fechaHora->format('Y-m-d H:i:s'),
            'fecha_formateada' => $fechaHora->format('d/m/Y'),
            'hora_formateada' => $fechaHora->format('H:i'),
            'duracion' => $duracion,
            'terapeuta_id' => $request->terapeuta_id,
        ];

        if (! $disponible) {
            // Obtener información sobre el conflicto
            $citaConflicto = Cita::where('terapeuta_id', $request->terapeuta_id)
                ->where('estado', '!=', 'cancelada')
                ->where(function ($query) use ($fechaHora, $duracion) {
                    $fechaFin = $fechaHora->copy()->addMinutes($duracion);
                    $query->where(function ($q) use ($fechaHora) {
                        $q->where('fecha_hora', '<=', $fechaHora)
                            ->whereRaw('DATE_ADD(fecha_hora, INTERVAL COALESCE(duracion, 60) MINUTE) > ?', [$fechaHora]);
                    })->orWhere(function ($q) use ($fechaHora, $fechaFin) {
                        $q->where('fecha_hora', '>=', $fechaHora)
                            ->where('fecha_hora', '<', $fechaFin);
                    });
                })
                ->first();

            if ($citaConflicto) {
                $response['conflicto'] = [
                    'cita_id' => $citaConflicto->id,
                    'hora_conflicto' => $citaConflicto->fecha_hora->format('H:i'),
                    'duracion_conflicto' => $citaConflicto->duracion ?? 60,
                ];
            }

            // Obtener horas disponibles para ese día
            $horasDisponibles = Cita::horasDisponibles(
                $fechaHora->format('Y-m-d'),
                $request->terapeuta_id,
                $duracion
            );

            $response['horas_disponibles'] = $horasDisponibles;
            $response['mensaje'] = empty($horasDisponibles)
                ? 'No hay horarios disponibles para esta fecha. Selecciona otra fecha.'
                : 'Horario ocupado. Elige una de las horas disponibles.';
        } else {
            $response['mensaje'] = 'Horario disponible';
        }

        return response()->json($response);
    }

    /**
     * Obtener horas disponibles para un terapeuta en una fecha específica
     */
    public function horasDisponibles(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'terapeuta_id' => 'required|exists:terapeutas,id',
            'duracion' => 'nullable|integer|min:15|max:240',
        ]);

        $horas = Cita::horasDisponibles(
            $request->fecha,
            $request->terapeuta_id,
            $request->duracion ?? 60
        );

        return response()->json([
            'fecha' => $request->fecha,
            'terapeuta_id' => $request->terapeuta_id,
            'horas_disponibles' => $horas,
            'total_horas' => count($horas),
        ]);
    }

    /**
     * Obtener fechas disponibles para un terapeuta en un rango de fechas
     */
    public function fechasDisponibles(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'terapeuta_id' => 'required|exists:terapeutas,id',
            'duracion' => 'nullable|integer|min:15|max:240',
        ]);

        $fechas = Cita::fechasDisponibles(
            $request->fecha_inicio,
            $request->fecha_fin,
            $request->terapeuta_id,
            $request->duracion ?? 60
        );

        return response()->json([
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'terapeuta_id' => $request->terapeuta_id,
            'fechas_disponibles' => $fechas,
            'total_fechas' => count($fechas),
        ]);
    }

    /**
     * Obtener el próximo horario disponible para un terapeuta
     */
    public function proximoHorarioDisponible(Request $request)
    {
        $request->validate([
            'terapeuta_id' => 'required|exists:terapeutas,id',
            'duracion' => 'nullable|integer|min:15|max:240',
        ]);

        $proximoHorario = Cita::proximoHorarioDisponible(
            $request->terapeuta_id,
            $request->duracion ?? 60
        );

        if (! $proximoHorario) {
            return response()->json([
                'mensaje' => 'No hay horarios disponibles en los próximos 30 días',
                'proximo_horario' => null,
            ], 404);
        }

        return response()->json([
            'proximo_horario' => $proximoHorario,
            'mensaje' => 'Próximo horario disponible encontrado',
        ]);
    }

    /**
     * Obtener disponibilidad del calendario para un terapeuta
     */
    public function calendarioDisponibilidad(Request $request)
    {
        $request->validate([
            'terapeuta_id' => 'required|exists:terapeutas,id',
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer|min:2024|max:2030',
            'duracion' => 'nullable|integer|min:15|max:240',
        ]);

        $terapeutaId = $request->terapeuta_id;
        $mes = $request->mes;
        $anio = $request->anio;
        $duracion = $request->duracion ?? 60;

        // Crear fecha de inicio y fin del mes
        $fechaInicio = Carbon::create($anio, $mes, 1);
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        // Obtener todas las citas del terapeuta para este mes
        $citasDelMes = Cita::where('terapeuta_id', $terapeutaId)
            ->where('estado', '!=', 'cancelada')
            ->whereBetween('fecha_hora', [$fechaInicio, $fechaFin])
            ->get()
            ->groupBy(function ($cita) {
                return $cita->fecha_hora->format('Y-m-d');
            });

        $calendario = [];
        $fecha = $fechaInicio->copy();

        while ($fecha->lte($fechaFin)) {
            $fechaStr = $fecha->format('Y-m-d');
            $diaSemana = $fecha->dayOfWeek;

            // Saltar domingos y fechas completamente pasadas (no incluir el día de hoy)
            if ($diaSemana === Carbon::SUNDAY || $fecha->isYesterday() || $fecha->lt(Carbon::today())) {
                $calendario[$fechaStr] = [
                    'fecha' => $fechaStr,
                    'dia' => $fecha->day,
                    'dia_semana' => $fecha->locale('es')->dayName,
                    'disponible' => false,
                    'motivo' => $diaSemana === Carbon::SUNDAY ? 'domingo' : 'fecha_pasada',
                    'horas_disponibles' => [],
                    'horas_ocupadas' => [],
                ];
            } else {
                $horasDisponibles = Cita::horasDisponibles($fechaStr, $terapeutaId, $duracion);
                $citasDelDia = $citasDelMes->get($fechaStr, collect());

                $horasOcupadas = $citasDelDia->map(function ($cita) {
                    return $cita->fecha_hora->format('H:i');
                })->toArray();

                $calendario[$fechaStr] = [
                    'fecha' => $fechaStr,
                    'dia' => $fecha->day,
                    'dia_semana' => $fecha->locale('es')->dayName,
                    'disponible' => ! empty($horasDisponibles),
                    'motivo' => empty($horasDisponibles) ? 'sin_horarios' : 'disponible',
                    'horas_disponibles' => $horasDisponibles,
                    'horas_ocupadas' => $horasOcupadas,
                    'total_disponibles' => count($horasDisponibles),
                    'total_ocupadas' => count($horasOcupadas),
                ];
            }

            $fecha->addDay();
        }

        return response()->json([
            'terapeuta_id' => $terapeutaId,
            'mes' => $mes,
            'anio' => $anio,
            'duracion' => $duracion,
            'calendario' => $calendario,
            'resumen' => [
                'dias_disponibles' => count(array_filter($calendario, function ($dia) {
                    return $dia['disponible'];
                })),
                'dias_ocupados' => count(array_filter($calendario, function ($dia) {
                    return ! $dia['disponible'] && $dia['motivo'] === 'sin_horarios';
                })),
                'domingos' => count(array_filter($calendario, function ($dia) {
                    return $dia['motivo'] === 'domingo';
                })),
            ],
        ]);
    }

    /**
     * Métodos específicos para la recepcionista
     */
    public function citasRecepcionista(Request $request)
    {
        try {
            $user = $request->user();

            // Verificar que el usuario sea recepcionista o administrador
            if (!in_array($user->rol_id, [1, 3])) {
                return response()->json(['error' => 'No autorizado.'], 403);
            }

            // Obtener parámetros de filtro
            $fechaInicio = $request->get('fecha_inicio', now()->format('Y-m-d'));
            $fechaFin = $request->get('fecha_fin', now()->addDays(7)->format('Y-m-d'));
            $estado = $request->get('estado');
            $terapeutaId = $request->get('terapeuta_id');

            $query = Cita::with([
                'paciente.usuario', 
                'terapeuta.usuario', 
                'pagoActual',
                'paquetePaciente'
            ]);

            // Filtros
            if ($fechaInicio && $fechaFin) {
                $query->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
            }

            if ($estado) {
                $query->where('estado', $estado);
            }

            if ($terapeutaId) {
                $query->where('terapeuta_id', $terapeutaId);
            }

            $citas = $query->orderBy('fecha_hora', 'asc')->get();

            // Agregar información de costo y estado de pago
            $citas = $citas->map(function ($cita) {
                $cita->costo_consulta = $this->calcularCostoCita($cita);
                $cita->pagada = $cita->pagoActual !== null;
                $cita->monto_pagado = $cita->pagoActual ? $cita->pagoActual->monto : 0;
                return $cita;
            });

            return response()->json([
                'success' => true,
                'data' => $citas,
                'message' => 'Citas obtenidas correctamente'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error en citasRecepcionista: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular el costo de una cita
     */
    private function calcularCostoCita($cita)
    {
        // Si la cita tiene un paquete asociado, el costo podría ser diferente
        if ($cita->paquetePaciente) {
            // Lógica para paquetes - por ahora retornamos un valor fijo
            return 800.00;
        }

        // Costo base según el tipo de cita o tarifa general
        $tarifaGeneral = \App\Models\Tarifa::where('tipo', 'General')->first();
        
        if ($tarifaGeneral) {
            return $tarifaGeneral->precio;
        }

        // Precio por defecto si no hay tarifa configurada
        return 1000.00;
    }

    /**
     * Procesar pago de una cita
     */
    public function procesarPago(Request $request, $citaId)
    {
        try {
            $user = $request->user();

            // Verificar que el usuario sea recepcionista o administrador
            if (!in_array($user->rol_id, [1, 3])) {
                return response()->json(['error' => 'No autorizado.'], 403);
            }

            // Validar datos del pago
            $validatedData = $request->validate([
                'monto' => 'required|numeric|min:0',
                'forma_pago' => 'required|in:efectivo,transferencia,terminal',
                'recibo' => 'nullable|string|max:100',
                'autorizacion' => 'nullable|string|max:100',
                'factura_emitida' => 'boolean'
            ]);

            // Buscar la cita
            $cita = Cita::with(['paciente.usuario', 'terapeuta.usuario', 'pagoActual'])
                         ->findOrFail($citaId);

            // Verificar si ya está pagada
            if ($cita->pagoActual) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta cita ya tiene un pago registrado'
                ], 400);
            }

            // Crear el pago
            $pago = \App\Models\Pago::create([
                'fecha_hora' => now(),
                'monto' => $validatedData['monto'],
                'forma_pago' => $validatedData['forma_pago'],
                'recibo' => $validatedData['recibo'] ?? null,
                'cita_id' => $citaId,
                'autorizacion' => $validatedData['autorizacion'] ?? null,
                'factura_emitida' => $validatedData['factura_emitida'] ?? false,
            ]);

            // Actualizar estado de la cita si está pendiente
            if ($cita->estado === 'agendada') {
                $cita->update(['estado' => 'atendida']);
            }

            // Crear bitácora del pago
            \Illuminate\Support\Facades\DB::table('bitacoras')->insert([
                'usuario_id' => $user->id,
                'accion' => 'pago_cita',
                'tabla' => 'pagos',
                'registro_id' => $pago->id,
                'fecha_hora' => now(),
                'detalle' => json_encode([
                    'descripcion' => "Pago procesado para cita del paciente {$cita->paciente->usuario->nombre}",
                    'cita_id' => $citaId,
                    'paciente' => $cita->paciente->usuario->nombre,
                    'monto' => $validatedData['monto'],
                    'forma_pago' => $validatedData['forma_pago'],
                    'fecha_cita' => $cita->fecha_hora,
                ])
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'pago' => $pago,
                    'cita' => $cita->load('pagoActual')
                ],
                'message' => 'Pago procesado exitosamente'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al procesar pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tarifas disponibles
     */
    public function obtenerTarifas()
    {
        try {
            $tarifas = \App\Models\Tarifa::all();

            return response()->json([
                'success' => true,
                'data' => $tarifas
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener tarifas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar una cita como completada y actualizar historial médico
     */
    public function completarCita(Request $request, $citaId)
    {
        $user = $request->user();
        
        // Verificar que el usuario es un terapeuta
        if ($user->rol_id !== 2) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        $cita = Cita::find($citaId);
        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        // Verificar que el terapeuta está asignado a esta cita
        if ($cita->terapeuta_id !== $user->id) {
            return response()->json(['error' => 'No autorizado para esta cita'], 403);
        }

        $data = $request->validate([
            'escala_dolor_eva_fin' => 'sometimes|integer|min:0|max:10',
            'observaciones_sesion' => 'sometimes|string',
            'estado' => 'sometimes|in:completada,cancelada,no_asistio',
        ]);

        // Actualizar la cita
        $cita->update([
            'estado' => $data['estado'] ?? 'completada',
            'escala_dolor_eva_fin' => $data['escala_dolor_eva_fin'] ?? null,
            'observaciones' => isset($data['observaciones_sesion']) ? 
                ($cita->observaciones ? $cita->observaciones . "\n\n--- Observaciones de la sesión ---\n" . $data['observaciones_sesion'] : $data['observaciones_sesion']) :
                $cita->observaciones
        ]);

        // Actualizar historial médico con la evolución
        if ($cita->estado === 'completada') {
            $historialController = new \App\Http\Controllers\HistorialMedicoController();
            $historialController->actualizarDespuesCita($cita);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cita marcada como completada',
            'cita' => $cita->load(['paciente.usuario', 'terapeuta.usuario'])
        ], 200);
    }
}
