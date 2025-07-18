<?php

namespace App\Http\Controllers;

use App\Models\Valoracion;
use Illuminate\Http\Request;

class ValoracionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(Valoracion::all(), 200);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'puntuacion' => 'required|integer|min:1|max:5',
            'fecha_hora' => 'required|date',
            'paciente_id' => 'required|exists:pacientes,id',
        ]);

        return response()->json(Valoracion::create($data), 201);
    }

    public function show(Valoracion $valoracion)
    {
        return response()->json($valoracion, 200);
    }

    public function update(Request $r, Valoracion $valoracion)
    {
        $data = $r->validate(['puntuacion' => 'sometimes|integer|min:1|max:5', 'fecha_hora' => 'sometimes|date']);
        $valoracion->update($data);

        return response()->json($valoracion, 200);
    }

    public function destroy(Valoracion $valoracion)
    {
        $valoracion->delete();

        return response()->json(['message' => 'Eliminado'], 200);
    }
}
