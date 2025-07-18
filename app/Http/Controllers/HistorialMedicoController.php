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
        $data = $r->validate(['paciente_id' => 'required|exists:pacientes,id', 'observaciones' => 'nullable|string']);

        return response()->json(HistorialMedico::create($data), 201);
    }

    public function show(HistorialMedico $historial)
    {
        return response()->json($historial, 200);
    }

    public function update(Request $r, HistorialMedico $historial)
    {
        $data = $r->validate(['observaciones' => 'sometimes|string']);
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
}
