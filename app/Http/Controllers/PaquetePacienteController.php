<?php

namespace App\Http\Controllers;

use App\Models\PaquetePaciente;
use Illuminate\Http\Request;

class PaquetePacienteController extends Controller
{
    public function index()
    {
        return response()->json(PaquetePaciente::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['paciente_id' => 'required|exists:pacientes,id', 'paquete_sesion_id' => 'required|exists:paquete_sesions,id', 'fecha_adquisicion' => 'required|date']);
        return response()->json(PaquetePaciente::create($data), 201);
    }
    public function show(PaquetePaciente $pp)
    {
        return response()->json($pp, 200);
    }
    public function update(Request $r, PaquetePaciente $pp)
    {
        $data = $r->validate(['fecha_adquisicion' => 'sometimes|date']);
        $pp->update($data);
        return response()->json($pp, 200);
    }
    public function destroy(PaquetePaciente $pp)
    {
        $pp->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
