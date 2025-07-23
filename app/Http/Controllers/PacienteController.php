<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use App\Models\Paciente;
use App\Models\Cita;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');

    }

    /**
     * Listar pacientes con paginación opcional.
     */
    public function index(Request $request)
    {
        // Si se solicita todos los datos sin paginación
        if ($request->query('all') === 'true') {
            return response()->json(Paciente::with('usuario')->get(), 200);
        }

        // Paginación estándar
        $perPage = $request->query('per_page', 20);
        $pacientes = Paciente::with('usuario')->paginate($perPage);

        return response()->json($pacientes, 200);
    }

    /**
     * Crear un nuevo paciente.
     */
    public function store(StorePacienteRequest $request)
    {
        $paciente = Paciente::create($request->validated());

        return response()->json($paciente, 201);
    }

    /**
     * Mostrar un paciente específico.
     */
    public function show(Paciente $paciente)
    {
        $paciente->load([
            'usuario',
            'historialMedico.registros' => function ($query) {
                $query->orderBy('Fecha_Hora', 'desc');
            },
            'citas' => function ($query) {
                $query->orderBy('fecha_hora', 'desc');
            },
            'valoraciones',
            'consentimientos'
        ]);
        
        return response()->json($paciente, 200);
    }

    /**
     * Actualizar un paciente.
     */
    public function update(UpdatePacienteRequest $request, Paciente $paciente)
    {
        $paciente->update($request->validated());

        return response()->json($paciente, 200);
    }

    /**
     * Eliminar un paciente.
     */
    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return response()->json(['message' => 'Paciente eliminado correctamente'], 200);
    }

    /**
     * Obtener pacientes del terapeuta autenticado
     */
    public function misPacientes(Request $request)
    {
        $user = $request->user();

        // Verificar que el usuario sea un terapeuta
        if ($user->rol_id !== 2) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Obtener pacientes únicos que han tenido citas con este terapeuta
        $pacientes = Paciente::whereHas('citas', function ($query) use ($user) {
            $query->where('terapeuta_id', $user->id);
        })
        ->with(['usuario', 'historialMedico'])
        ->withCount(['citas' => function ($query) use ($user) {
            $query->where('terapeuta_id', $user->id);
        }])
        ->get()
        ->map(function ($paciente) use ($user) {
            // Obtener la última cita con este terapeuta
            $ultimaCita = Cita::where('paciente_id', $paciente->id)
                ->where('terapeuta_id', $user->id)
                ->orderBy('fecha_hora', 'desc')
                ->first();

            $paciente->ultima_cita = $ultimaCita;
            return $paciente;
        });

        return response()->json($pacientes, 200);
    }

    /**
     * Crear o actualizar historial médico del paciente
     */
    public function crearHistorialMedico(Request $request, $pacienteId)
    {
        $user = $request->user();

        // Verificar que el usuario sea un terapeuta
        if ($user->rol_id !== 2) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'observacion_general' => 'required|string|max:2000',
            'alergias' => 'nullable|string|max:1000',
            'medicamentos_actuales' => 'nullable|string|max:1000',
            'antecedentes_familiares' => 'nullable|string|max:1000',
            'cirugias_previas' => 'nullable|string|max:1000',
            'lesiones_previas' => 'nullable|string|max:1000',
        ]);

        $paciente = Paciente::findOrFail($pacienteId);

        // Verificar que el paciente tenga citas con este terapeuta
        $tieneCitas = \App\Models\Cita::where('paciente_id', $pacienteId)
            ->where('terapeuta_id', $user->id)
            ->exists();

        if (!$tieneCitas) {
            return response()->json(['error' => 'No tienes autorización para editar este paciente'], 403);
        }

        // Crear o actualizar historial médico
        $historial = \App\Models\HistorialMedico::updateOrCreate(
            ['paciente_id' => $pacienteId],
            [
                'observacion_general' => $request->observacion_general,
                'fecha_creacion' => now(),
                'alergias' => $request->alergias,
                'medicamentos_actuales' => $request->medicamentos_actuales,
                'antecedentes_familiares' => $request->antecedentes_familiares,
                'cirugias_previas' => $request->cirugias_previas,
                'lesiones_previas' => $request->lesiones_previas,
            ]
        );

        // No es necesario actualizar referencia, la relación es directa por paciente_id

        return response()->json([
            'message' => 'Historial médico actualizado correctamente',
            'historial' => $historial
        ], 200);
    }

    /**
     * Agregar registro al historial médico
     */
    public function agregarRegistroHistorial(Request $request, $pacienteId)
    {
        $user = $request->user();

        // Verificar que el usuario sea un terapeuta
        if ($user->rol_id !== 2) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'tipo' => 'required|string|in:consulta,tratamiento,evaluacion,nota_general',
            'descripcion' => 'required|string|max:2000',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $paciente = Paciente::findOrFail($pacienteId);

        // Verificar que el paciente tenga citas con este terapeuta
        $tieneCitas = \App\Models\Cita::where('paciente_id', $pacienteId)
            ->where('terapeuta_id', $user->id)
            ->exists();

        if (!$tieneCitas) {
            return response()->json(['error' => 'No tienes autorización para editar este paciente'], 403);
        }

        // Obtener o crear historial médico
        $historial = \App\Models\HistorialMedico::firstOrCreate(
            ['paciente_id' => $pacienteId],
            [
                'observacion_general' => 'Historial creado automáticamente',
                'fecha_creacion' => now()
            ]
        );

        // Crear el registro usando el modelo existente
        $registro = \App\Models\Registro::create([
            'Historial_Medico_Id' => $historial->id,
            'Fecha_Hora' => now(),
            'Motivo_Visita' => $request->descripcion,
            'Antecedentes' => $request->observaciones ?? '',
        ]);

        return response()->json([
            'message' => 'Registro agregado correctamente',
            'registro' => $registro
        ], 201);
    }

    /**
     * Agregar nota simple al paciente
     */
    public function agregarNotaPaciente(Request $request, $pacienteId)
    {
        $user = $request->user();

        // Verificar que el usuario sea un terapeuta
        if ($user->rol_id !== 2) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'nota' => 'required|string|max:2000',
        ]);

        $paciente = Paciente::findOrFail($pacienteId);

        // Verificar que el paciente tenga citas con este terapeuta
        $tieneCitas = \App\Models\Cita::where('paciente_id', $pacienteId)
            ->where('terapeuta_id', $user->id)
            ->exists();

        if (!$tieneCitas) {
            return response()->json(['error' => 'No tienes autorización para editar este paciente'], 403);
        }

        // Obtener o crear historial médico
        $historial = \App\Models\HistorialMedico::firstOrCreate(
            ['paciente_id' => $pacienteId],
            [
                'observacion_general' => 'Historial creado automáticamente',
                'fecha_creacion' => now()
            ]
        );

        // Crear el registro como nota
        $registro = \App\Models\Registro::create([
            'Historial_Medico_Id' => $historial->id,
            'Fecha_Hora' => now(),
            'Motivo_Visita' => 'Nota del terapeuta: ' . $request->nota,
            'Antecedentes' => 'Nota agregada por ' . $user->nombre . ' el ' . now()->format('d/m/Y H:i'),
        ]);

        return response()->json([
            'message' => 'Nota agregada correctamente',
            'registro' => $registro
        ], 201);
    }
}
