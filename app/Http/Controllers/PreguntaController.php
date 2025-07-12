<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
use Illuminate\Http\Request;

class PreguntaController extends Controller
{
    public function index()
    {
        return response()->json(Pregunta::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['encuesta_id' => 'required|exists:encuestas,id', 'texto' => 'required|string']);
        return response()->json(Pregunta::create($data), 201);
    }
    public function show(Pregunta $pregunta)
    {
        return response()->json($pregunta, 200);
    }
    public function update(Request $r, Pregunta $pregunta)
    {
        $data = $r->validate(['texto' => 'sometimes|string']);
        $pregunta->update($data);
        return response()->json($pregunta, 200);
    }
    public function destroy(Pregunta $pregunta)
    {
        $pregunta->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
