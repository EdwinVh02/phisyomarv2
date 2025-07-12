<?php

namespace App\Http\Controllers;

use App\Models\ConsentimientoInformado;
use Illuminate\Http\Request;

class ConsentimientoInformadoController extends Controller
{
    public function index()
    {
        return response()->json(ConsentimientoInformado::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['paciente_id' => 'required|exists:pacientes,id', 'fecha_firma' => 'required|date', 'documento' => 'required|string']);
        return response()->json(ConsentimientoInformado::create($data), 201);
    }
    public function show(ConsentimientoInformado $ci)
    {
        return response()->json($ci, 200);
    }
    public function update(Request $r, ConsentimientoInformado $ci)
    {
        $data = $r->validate(['documento' => 'sometimes']);
        $ci->update($data);
        return response()->json($ci, 200);
    }
    public function destroy(ConsentimientoInformado $ci)
    {
        $ci->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
