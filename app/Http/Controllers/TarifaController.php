<?php

namespace App\Http\Controllers;

use App\Models\Tarifa;
use Illuminate\Http\Request;

class TarifaController extends Controller
{
    public function index()
    {
        return response()->json(Tarifa::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['descripcion' => 'required|string', 'monto' => 'required|numeric']);
        return response()->json(Tarifa::create($data), 201);
    }
    public function show(Tarifa $tarifa)
    {
        return response()->json($tarifa, 200);
    }
    public function update(Request $r, Tarifa $tarifa)
    {
        $data = $r->validate(['descripcion' => 'sometimes|string', 'monto' => 'sometimes|numeric']);
        $tarifa->update($data);
        return response()->json($tarifa, 200);
    }
    public function destroy(Tarifa $tarifa)
    {
        $tarifa->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
