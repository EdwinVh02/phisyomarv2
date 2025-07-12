<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    public function index()
    {
        return response()->json(Especialidad::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['nombre' => 'required|string|unique:especialidads,nombre']);
        return response()->json(Especialidad::create($data), 201);
    }
    public function show(Especialidad $especialidad)
    {
        return response()->json($especialidad, 200);
    }
    public function update(Request $r, Especialidad $especialidad)
    {
        $data = $r->validate(['nombre' => 'sometimes|string|unique:especialidads,nombre,' . $especialidad->id]);
        $especialidad->update($data);
        return response()->json($especialidad, 200);
    }
    public function destroy(Especialidad $especialidad)
    {
        $especialidad->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
