<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function index()
    {
        return response()->json(Cita::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['fecha_hora' => 'required|date', 'paciente_id' => 'required|exists:pacientes,id', 'terapeuta_id' => 'required|exists:terapeutas,id']);
        return response()->json(Cita::create($data), 201);
    }
    public function show(Cita $cita)
    {
        return response()->json($cita, 200);
    }
    public function update(Request $r, Cita $cita)
    {
        $data = $r->validate(['fecha_hora' => 'sometimes|date']);
        $cita->update($data);
        return response()->json($cita, 200);
    }
    public function destroy(Cita $cita)
    {
        $cita->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
