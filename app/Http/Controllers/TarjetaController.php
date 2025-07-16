<?php

namespace App\Http\Controllers;

use App\Models\Tarjeta;
use Illuminate\Http\Request;

class TarjetaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(Tarjeta::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate([
            'pago_id' => 'required|exists:pagos,id',
            'numero' => 'required|string',
            'expiracion' => 'required|date'
        ]);
        return response()->json(Tarjeta::create($data), 201);
    }
    public function show(Tarjeta $tarjeta)
    {
        return response()->json($tarjeta, 200);
    }
    public function update(Request $r, Tarjeta $tarjeta)
    {
        $data = $r->validate(['numero' => 'sometimes|string', 'expiracion' => 'sometimes|date']);
        $tarjeta->update($data);
        return response()->json($tarjeta, 200);
    }
    public function destroy(Tarjeta $tarjeta)
    {
        $tarjeta->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
