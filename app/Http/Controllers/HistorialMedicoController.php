<?php

namespace App\Http\Controllers;

use App\Models\HistorialMedico;
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
}
