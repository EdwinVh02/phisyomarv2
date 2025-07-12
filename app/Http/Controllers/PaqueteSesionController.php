<?php

namespace App\Http\Controllers;

use App\Models\PaqueteSesion;
use Illuminate\Http\Request;

class PaqueteSesionController extends Controller
{
    public function index()
    {
        return response()->json(PaqueteSesion::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['nombre' => 'required|string', 'numero_sesiones' => 'required|integer', 'descuento' => 'required|numeric']);
        return response()->json(PaqueteSesion::create($data), 201);
    }
    public function show(PaqueteSesion $paqueteSesion)
    {
        return response()->json($paqueteSesion, 200);
    }
    public function update(Request $r, PaqueteSesion $paqueteSesion)
    {
        $data = $r->validate(['nombre' => 'sometimes|string', 'numero_sesiones' => 'sometimes|integer', 'descuento' => 'sometimes|numeric']);
        $paqueteSesion->update($data);
        return response()->json($paqueteSesion, 200);
    }
    public function destroy(PaqueteSesion $paqueteSesion)
    {
        $paqueteSesion->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
