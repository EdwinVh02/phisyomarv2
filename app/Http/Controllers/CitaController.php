<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\UpdateCitaRequest;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function __construct()
    {
        // Sin middleware global - se aplicará en las rutas
    }

    public function index()
    {
        return response()->json(Cita::all(), 200);
    }
    public function store(StoreCitaRequest $request)
    {
        $data = $request->validated();
        return response()->json(Cita::create($data), 201);
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
        return response()->json(['message' => 'Eliminado'], 200);
    }

    // Métodos específicos para pacientes
    public function misCitas(Request $request)
    {
        $user = $request->user();
        
        // Verificar que el usuario sea un paciente
        if ($user->rol_id !== 4) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $citas = Cita::where('paciente_id', $user->id)
                    ->with(['terapeuta.usuario', 'registro'])
                    ->orderBy('fecha_hora', 'desc')
                    ->get();

        return response()->json($citas, 200);
    }

    public function agendarCita(Request $request)
    {
        $user = $request->user();
        
        // Si no hay usuario (modo de prueba), usar paciente ID 200
        if (!$user) {
            $pacienteId = 200; // ID del paciente de prueba creado
            \Log::info('Modo de prueba - usando paciente ID: ' . $pacienteId);
        } else {
            // Verificar que el usuario sea un paciente
            if ($user->rol_id !== 4) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            // Verificar que existe el registro del paciente
            $paciente = \App\Models\Paciente::find($user->id);
            if (!$paciente) {
                return response()->json(['error' => 'Registro de paciente no encontrado'], 404);
            }
            $pacienteId = $user->id;
        }

        // Si no se proporciona terapeuta_id en modo de prueba, usar ID 100
        if (!$user && !$request->has('terapeuta_id')) {
            $request->merge(['terapeuta_id' => 100]); // ID del terapeuta de prueba
        }

        $validatedData = $request->validate([
            'fecha_hora' => 'required|date',
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
        ]);

        try {
            // Log para debugging
            \Log::info('Intentando crear cita', [
                'paciente_id' => $pacienteId,
                'terapeuta_id' => $validatedData['terapeuta_id'],
                'fecha_hora' => $validatedData['fecha_hora'],
                'tipo' => $validatedData['tipo'],
                'motivo' => $validatedData['motivo']
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
                'ubicacion' => !empty($validatedData['ubicacion']) ? $validatedData['ubicacion'] : null,
                'equipo_asignado' => !empty($validatedData['equipo_asignado']) ? $validatedData['equipo_asignado'] : null,
                'observaciones' => !empty($validatedData['observaciones']) ? $validatedData['observaciones'] : null,
                'escala_dolor_eva_inicio' => !empty($validatedData['escala_dolor_eva_inicio']) ? (int)$validatedData['escala_dolor_eva_inicio'] : null,
                'como_fue_lesion' => !empty($validatedData['como_fue_lesion']) ? $validatedData['como_fue_lesion'] : null,
                'antecedentes_patologicos' => !empty($validatedData['antecedentes_patologicos']) ? $validatedData['antecedentes_patologicos'] : null,
                'antecedentes_no_patologicos' => !empty($validatedData['antecedentes_no_patologicos']) ? $validatedData['antecedentes_no_patologicos'] : null,
            ]);

            return response()->json([
                'message' => 'Cita agendada exitosamente',
                'cita' => $cita->load(['terapeuta.usuario'])
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('Error al crear cita: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al agendar la cita',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelarCita(Request $request, $id)
    {
        $user = $request->user();
        
        // Verificar que el usuario sea un paciente
        if ($user->rol_id !== 4) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $cita = Cita::where('id', $id)
                   ->where('paciente_id', $user->id)
                   ->first();

        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        if ($cita->estado === 'cancelada') {
            return response()->json(['error' => 'La cita ya está cancelada'], 400);
        }

        $cita->update(['estado' => 'cancelada']);

        return response()->json(['message' => 'Cita cancelada exitosamente', 'cita' => $cita], 200);
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
}
