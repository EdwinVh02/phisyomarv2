<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use Illuminate\Http\Request;

class EncuestaController extends Controller
{
    public function index()
    {
        return response()->json(Encuesta::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['nombre' => 'required|string']);
        return response()->json(Encuesta::create($data), 201);
    }
    public function show(Encuesta $encuesta)
    {
        return response()->json($encuesta, 200);
    }
    public function update(Request $r, Encuesta $encuesta)
    {
        $data = $r->validate(['nombre' => 'sometimes|string']);
        $encuesta->update($data);
        return response()->json($encuesta, 200);
    }
    public function destroy(Encuesta $encuesta)
    {
        $encuesta->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
