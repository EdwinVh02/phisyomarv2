<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEncuestaRequest;
use App\Http\Requests\UpdateEncuestaRequest;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Cita;
use Illuminate\Http\Request;

class EncuestaController extends BaseResourceController
{
    /**
     * Get the model class for this controller
     */
    protected function getModelClass(): string
    {
        return Encuesta::class;
    }

    /**
     * Get the store request class
     */
    protected function getStoreRequestClass(): ?string
    {
        return StoreEncuestaRequest::class;
    }

    /**
     * Get the update request class
     */
    protected function getUpdateRequestClass(): ?string
    {
        return UpdateEncuestaRequest::class;
    }

    public function misEncuestas()
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

        // Obtener las citas del paciente que tienen encuestas disponibles
        $citas = Cita::where('paciente_id', $paciente->id)
            ->where('estado', 'atendida')
            ->with(['terapeuta.usuario', 'tratamiento'])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        // Obtener las respuestas existentes del paciente
        $respuestasExistentes = Respuesta::where('paciente_id', $paciente->id)
            ->pluck('cita_id')
            ->toArray();

        // Crear encuestas para las citas
        $encuestas = $citas->map(function ($cita) use ($respuestasExistentes) {
            $yaRespondida = in_array($cita->id, $respuestasExistentes);
            $fechaLimite = $cita->fecha_hora->copy()->addDays(7);
            $estaVencida = now()->gt($fechaLimite) && !$yaRespondida;
            
            $estado = $yaRespondida ? 'completada' : ($estaVencida ? 'vencida' : 'pendiente');
            
            // Calcular calificación promedio si está completada
            $calificacionPromedio = null;
            if ($yaRespondida) {
                $respuestas = Respuesta::where('paciente_id', $cita->paciente_id)
                    ->where('cita_id', $cita->id)
                    ->whereIn('tipo', ['rating', 'escala'])
                    ->get();
                
                if ($respuestas->count() > 0) {
                    $calificacionPromedio = $respuestas->avg('texto');
                }
            }

            return [
                'id' => $cita->id,
                'titulo' => 'Evaluación de Consulta - ' . $cita->terapeuta->usuario->nombre . ' ' . $cita->terapeuta->usuario->apellido_paterno,
                'fecha' => $cita->fecha_hora->format('Y-m-d'),
                'estado' => $estado,
                'terapeuta' => $cita->terapeuta->usuario->nombre . ' ' . $cita->terapeuta->usuario->apellido_paterno,
                'tipoConsulta' => $cita->tratamiento->nombre ?? 'Consulta General',
                'fechaConsulta' => $cita->fecha_hora->format('Y-m-d'),
                'fechaLimite' => $fechaLimite->format('Y-m-d'),
                'calificacionPromedio' => $calificacionPromedio,
                'preguntas' => $this->obtenerPreguntasEncuesta($cita->id, $yaRespondida)
            ];
        });

        return response()->json([
            'encuestas' => $encuestas,
            'resumen' => [
                'total' => $encuestas->count(),
                'completadas' => $encuestas->where('estado', 'completada')->count(),
                'pendientes' => $encuestas->where('estado', 'pendiente')->count(),
                'vencidas' => $encuestas->where('estado', 'vencida')->count(),
            ]
        ], 200);
    }

    public function responderEncuesta(Request $request, $citaId)
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

        // Verificar que la cita pertenece al paciente
        $cita = Cita::where('id', $citaId)
            ->where('paciente_id', $paciente->id)
            ->first();
        
        if (!$cita) {
            return response()->json(['error' => 'Cita no encontrada'], 404);
        }

        // Verificar que no haya respondido ya
        $respuestaExistente = Respuesta::where('paciente_id', $paciente->id)
            ->where('cita_id', $citaId)
            ->exists();

        if ($respuestaExistente) {
            return response()->json(['error' => 'Ya has respondido esta encuesta'], 400);
        }

        // Validar respuestas
        $request->validate([
            'respuestas' => 'required|array',
            'respuestas.*.pregunta_id' => 'required|integer',
            'respuestas.*.respuesta' => 'required',
            'respuestas.*.tipo' => 'required|string|in:rating,texto,escala'
        ]);

        // Guardar respuestas
        foreach ($request->respuestas as $respuestaData) {
            Respuesta::create([
                'texto' => $respuestaData['respuesta'],
                'tipo' => $respuestaData['tipo'],
                'pregunta_id' => $respuestaData['pregunta_id'],
                'paciente_id' => $paciente->id,
                'cita_id' => $citaId,
                'fecha_respuesta' => now(),
            ]);
        }

        return response()->json(['message' => 'Encuesta respondida exitosamente'], 201);
    }

    private function obtenerPreguntasEncuesta($citaId, $yaRespondida)
    {
        // Preguntas estándar para la encuesta de satisfacción
        $preguntasEstandar = [
            [
                'id' => 1,
                'pregunta' => '¿Cómo calificarías la atención recibida?',
                'tipo' => 'rating',
                'respuesta' => null
            ],
            [
                'id' => 2,
                'pregunta' => '¿El terapeuta explicó claramente tu diagnóstico?',
                'tipo' => 'rating',
                'respuesta' => null
            ],
            [
                'id' => 3,
                'pregunta' => '¿Te sientes satisfecho con el tratamiento?',
                'tipo' => 'rating',
                'respuesta' => null
            ],
            [
                'id' => 4,
                'pregunta' => 'Comentarios adicionales',
                'tipo' => 'texto',
                'respuesta' => null
            ]
        ];

        // Si ya respondió, cargar las respuestas
        if ($yaRespondida) {
            $respuestas = Respuesta::where('cita_id', $citaId)
                ->get()
                ->keyBy('pregunta_id');

            foreach ($preguntasEstandar as &$pregunta) {
                if (isset($respuestas[$pregunta['id']])) {
                    $pregunta['respuesta'] = $respuestas[$pregunta['id']]->texto;
                }
            }
        }

        return $preguntasEstandar;
    }
}
