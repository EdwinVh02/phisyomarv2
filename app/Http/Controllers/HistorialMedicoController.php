<?php

namespace App\Http\Controllers;

use App\Models\HistorialMedico;
use App\Models\Registro;
use App\Models\Cita;
use Illuminate\Http\Request;

class HistorialMedicoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(HistorialMedico::all(), 200);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'fecha_creacion' => 'nullable|date',
            'observacion_general' => 'nullable|string',
            'motivo_consulta' => 'nullable|string',
            'alergias' => 'nullable|string',
            'medicamentos_actuales' => 'nullable|string',
            'antecedentes_familiares' => 'nullable|string',
            'cirugias_previas' => 'nullable|string',
            'lesiones_previas' => 'nullable|string',
            'inspeccion_general' => 'nullable|string',
            'rango_movimiento' => 'nullable|string',
            'fuerza_muscular' => 'nullable|string',
            'pruebas_especiales' => 'nullable|string',
            'dolor_eva' => 'nullable|integer|min:0|max:10',
            'diagnostico_fisioterapeutico' => 'nullable|string',
            'frecuencia_sesiones' => 'nullable|string',
            'tecnicas_propuestas' => 'nullable|string',
            'objetivos_corto_plazo' => 'nullable|string',
            'objetivos_mediano_plazo' => 'nullable|string',
            'objetivos_largo_plazo' => 'nullable|string',
            'evolucion_notas_seguimiento' => 'nullable|string',
            'firma_fisioterapeuta' => 'nullable|string',
            'firma_paciente' => 'nullable|string'
        ]);

        // Establecer fecha de creación si no se proporciona
        if (!isset($data['fecha_creacion'])) {
            $data['fecha_creacion'] = now();
        }

        return response()->json(HistorialMedico::create($data), 201);
    }

    public function show(HistorialMedico $historial)
    {
        return response()->json($historial, 200);
    }

    public function update(Request $r, HistorialMedico $historial)
    {
        $data = $r->validate([
            'fecha_creacion' => 'sometimes|date',
            'observacion_general' => 'sometimes|string',
            'motivo_consulta' => 'sometimes|string',
            'alergias' => 'sometimes|string',
            'medicamentos_actuales' => 'sometimes|string',
            'antecedentes_familiares' => 'sometimes|string',
            'cirugias_previas' => 'sometimes|string',
            'lesiones_previas' => 'sometimes|string',
            'inspeccion_general' => 'sometimes|string',
            'rango_movimiento' => 'sometimes|string',
            'fuerza_muscular' => 'sometimes|string',
            'pruebas_especiales' => 'sometimes|string',
            'dolor_eva' => 'sometimes|integer|min:0|max:10',
            'diagnostico_fisioterapeutico' => 'sometimes|string',
            'frecuencia_sesiones' => 'sometimes|string',
            'tecnicas_propuestas' => 'sometimes|string',
            'objetivos_corto_plazo' => 'sometimes|string',
            'objetivos_mediano_plazo' => 'sometimes|string',
            'objetivos_largo_plazo' => 'sometimes|string',
            'evolucion_notas_seguimiento' => 'sometimes|string',
            'firma_fisioterapeuta' => 'sometimes|string',
            'firma_paciente' => 'sometimes|string'
        ]);
        
        $historial->update($data);

        return response()->json($historial, 200);
    }

    public function destroy(HistorialMedico $historial)
    {
        $historial->delete();

        return response()->json(['message' => 'Eliminado'], 200);
    }

    public function miHistorial()
    {
        $user = auth()->user();
        
        // Verificar que el usuario es un paciente
        if ($user->rol_id !== 4) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        // Obtener el paciente
        $paciente = $user->paciente;
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Obtener el historial médico del paciente
        $historial = HistorialMedico::where('paciente_id', $paciente->id)
            ->with(['registros' => function($query) {
                $query->orderBy('fecha_hora', 'desc');
            }])
            ->first();

        // Obtener las citas del paciente con información médica
        $citas = Cita::where('paciente_id', $paciente->id)
            ->with(['terapeuta.usuario', 'tratamiento'])
            ->whereNotNull('observaciones')
            ->orderBy('fecha_hora', 'desc')
            ->get();

        // Combinar información del historial y citas
        $historialCompleto = [
            'historial_medico' => $historial,
            'consultas' => $citas->map(function ($cita) {
                return [
                    'id' => $cita->id,
                    'fecha' => $cita->fecha_hora->format('Y-m-d'),
                    'hora' => $cita->fecha_hora->format('H:i'),
                    'tipo' => $cita->tipo ?? 'Consulta General',
                    'terapeuta' => $cita->terapeuta->usuario->nombre . ' ' . $cita->terapeuta->usuario->apellido_paterno,
                    'diagnostico' => $cita->como_fue_lesion ?? 'Sin diagnóstico específico',
                    'tratamiento' => $cita->tratamiento->nombre ?? 'Tratamiento estándar',
                    'observaciones' => $cita->observaciones,
                    'estado' => $cita->estado,
                    'duracion' => $cita->duracion,
                    'ubicacion' => $cita->ubicacion ?? 'Consultorio principal',
                    'escala_dolor_inicio' => $cita->escala_dolor_eva_inicio,
                    'escala_dolor_fin' => $cita->escala_dolor_eva_fin,
                    'antecedentes_patologicos' => $cita->antecedentes_patologicos,
                    'antecedentes_no_patologicos' => $cita->antecedentes_no_patologicos,
                ];
            })
        ];

        return response()->json($historialCompleto, 200);
    }

    /**
     * Crear o actualizar historial médico basado en los datos de una cita
     */
    public function crearOActualizarDesdeCita($cita)
    {
        // Buscar historial existente del paciente
        $historial = HistorialMedico::where('paciente_id', $cita->paciente_id)->first();

        $datosHistorial = [
            'paciente_id' => $cita->paciente_id,
            'fecha_creacion' => $historial ? $historial->fecha_creacion : now(),
            'motivo_consulta' => $cita->motivo,
            'dolor_eva' => $cita->escala_dolor_eva_inicio,
            'lesiones_previas' => $cita->como_fue_lesion,
            'antecedentes_familiares' => $cita->antecedentes_patologicos,
            'observacion_general' => $cita->observaciones,
        ];

        if ($historial) {
            // Actualizar solo los campos que no están vacíos
            $datosParaActualizar = array_filter($datosHistorial, function($value) {
                return !is_null($value) && $value !== '';
            });
            
            $historial->update($datosParaActualizar);
            return $historial;
        } else {
            // Crear nuevo historial
            return HistorialMedico::create($datosHistorial);
        }
    }

    /**
     * Actualizar historial médico después de una cita completada
     */
    public function actualizarDespuesCita($cita)
    {
        $historial = HistorialMedico::where('paciente_id', $cita->paciente_id)->first();
        
        if ($historial && $cita->escala_dolor_eva_fin) {
            // Agregar notas de evolución
            $nuevaEvolucion = "Sesión del " . $cita->fecha_hora->format('d/m/Y') . 
                            " - Dolor inicial: " . ($cita->escala_dolor_eva_inicio ?? 'N/A') . 
                            "/10, Dolor final: " . $cita->escala_dolor_eva_fin . "/10";
            
            if ($cita->observaciones) {
                $nuevaEvolucion .= " - Observaciones: " . $cita->observaciones;
            }

            $evolucionActual = $historial->evolucion_notas_seguimiento ?? '';
            $evolucionActualizada = $evolucionActual ? 
                $evolucionActual . "\n\n" . $nuevaEvolucion : 
                $nuevaEvolucion;

            $historial->update([
                'evolucion_notas_seguimiento' => $evolucionActualizada
            ]);
        }

        return $historial;
    }

    /**
     * Método para que los terapeutas actualicen el historial médico de sus pacientes
     */
    public function actualizarHistorialTerapeuta(Request $request, $pacienteId)
    {
        $user = $request->user();
        
        // Verificar que el usuario es un terapeuta
        if ($user->rol_id !== 2) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        $data = $request->validate([
            'observacion_general' => 'sometimes|string',
            'motivo_consulta' => 'sometimes|string',
            'alergias' => 'sometimes|string',
            'medicamentos_actuales' => 'sometimes|string',
            'antecedentes_familiares' => 'sometimes|string',
            'cirugias_previas' => 'sometimes|string',
            'lesiones_previas' => 'sometimes|string',
            'inspeccion_general' => 'sometimes|string',
            'rango_movimiento' => 'sometimes|string',
            'fuerza_muscular' => 'sometimes|string',
            'pruebas_especiales' => 'sometimes|string',
            'dolor_eva' => 'sometimes|integer|min:0|max:10',
            'diagnostico_fisioterapeutico' => 'sometimes|string',
            'frecuencia_sesiones' => 'sometimes|string',
            'tecnicas_propuestas' => 'sometimes|string',
            'objetivos_corto_plazo' => 'sometimes|string',
            'objetivos_mediano_plazo' => 'sometimes|string',
            'objetivos_largo_plazo' => 'sometimes|string',
            'evolucion_notas_seguimiento' => 'sometimes|string',
            'firma_fisioterapeuta' => 'sometimes|string',
        ]);

        // Buscar o crear historial
        $historial = HistorialMedico::where('paciente_id', $pacienteId)->first();
        
        if (!$historial) {
            $data['paciente_id'] = $pacienteId;
            $data['fecha_creacion'] = now();
            $historial = HistorialMedico::create($data);
        } else {
            $historial->update($data);
        }

        return response()->json([
            'success' => true,
            'historial' => $historial,
            'message' => 'Historial médico actualizado correctamente'
        ], 200);
    }
}
