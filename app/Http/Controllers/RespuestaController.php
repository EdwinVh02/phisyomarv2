<?php

namespace App\Http\Controllers;

use App\Models\Respuesta;
use Illuminate\Http\Request;

class RespuestaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(Respuesta::all(), 200);
    }

    public function store(Request $r)
    {
        $data = $r->validate(['pregunta_id' => 'required|exists:preguntas,id', 'paciente_id' => 'required|exists:pacientes,id', 'respuesta' => 'required|string']);

        return response()->json(Respuesta::create($data), 201);
    }

    public function show(Respuesta $respuesta)
    {
        return response()->json($respuesta, 200);
    }

    public function update(Request $r, Respuesta $respuesta)
    {
        $data = $r->validate(['respuesta' => 'sometimes|string']);
        $respuesta->update($data);

        return response()->json($respuesta, 200);
    }

    public function destroy(Respuesta $respuesta)
    {
        $respuesta->delete();

        return response()->json(['message' => 'Eliminado'], 200);
    }
}
