<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use Illuminate\Http\Request;

class TratamientoController extends Controller
{
    public function index()
    {
        return response()->json(Tratamiento::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate([
            'nombre' => 'required|string',
            'tarifa_id' => 'required|exists:tarifas,id'
        ]);
        return response()->json(Tratamiento::create($data), 201);
    }
    public function show(Tratamiento $tratamiento)
    {
        return response()->json($tratamiento, 200);
    }
    public function update(Request $r, Tratamiento $tratamiento)
    {
        $data = $r->validate(['nombre' => 'sometimes|string', 'tarifa_id' => 'sometimes|exists:tarifas,id']);
        $tratamiento->update($data);
        return response()->json($tratamiento, 200);
    }
    public function destroy(Tratamiento $tratamiento)
    {
        $tratamiento->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
