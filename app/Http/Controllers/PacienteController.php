<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    /**
     * Listar pacientes con paginación opcional.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 20);
        return response()->json(Paciente::paginate($perPage), 200);
    }

    /**
     * Crear un nuevo paciente.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'usuario_id'                     => 'required|exists:usuarios,id',
            'contacto_emergencia_nombre'     => 'nullable|string|max:100',
            'contacto_emergencia_telefono'   => 'nullable|string|max:20',
            'contacto_emergencia_parentesco' => 'nullable|string|max:50',
            'tutor_nombre'                   => 'nullable|string|max:100',
            'tutor_telefono'                 => 'nullable|string|max:20',
            'tutor_parentesco'               => 'nullable|string|max:50',
            'tutor_direccion'                => 'nullable|string|max:150',
            'historial_medico_id'            => 'nullable|exists:historial_medicos,id',
            // Si tienes más campos, agrégalos aquí
        ]);
        $paciente = Paciente::create($data);
        return response()->json($paciente, 201);
    }

    /**
     * Mostrar un paciente específico.
     */
    public function show(Paciente $paciente)
    {
        return response()->json($paciente, 200);
    }

    /**
     * Actualizar un paciente.
     */
    public function update(Request $request, Paciente $paciente)
    {
        $data = $request->validate([
            'contacto_emergencia_nombre'     => 'nullable|string|max:100',
            'contacto_emergencia_telefono'   => 'nullable|string|max:20',
            'contacto_emergencia_parentesco' => 'nullable|string|max:50',
            'tutor_nombre'                   => 'nullable|string|max:100',
            'tutor_telefono'                 => 'nullable|string|max:20',
            'tutor_parentesco'               => 'nullable|string|max:50',
            'tutor_direccion'                => 'nullable|string|max:150',
            'historial_medico_id'            => 'nullable|exists:historial_medicos,id',
            // Si tienes más campos, agrégalos aquí
        ]);
        $paciente->update($data);
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
}
