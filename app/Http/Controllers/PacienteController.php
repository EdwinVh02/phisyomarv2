<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
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
        $perPage = $request->query('per_page', 20);
        return response()->json(Paciente::paginate($perPage), 200);
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
}
