<?php

namespace App\Http\Controllers;

use App\Models\Terapeuta;
use Illuminate\Http\Request;

class TerapeutaController extends Controller
{
    public function index()
    {
        return response()->json(Terapeuta::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['usuario_id' => 'required|exists:usuarios,id', 'cedula_profesional' => 'required|string']);
        return response()->json(Terapeuta::create($data), 201);
    }
    public function show(Terapeuta $terapeuta)
    {
        return response()->json($terapeuta, 200);
    }
    public function update(Request $r, Terapeuta $terapeuta)
    {
        $data = $r->validate(['cedula_profesional' => 'sometimes|string']);
        $terapeuta->update($data);
        return response()->json($terapeuta, 200);
    }
    public function destroy(Terapeuta $terapeuta)
    {
        $terapeuta->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
