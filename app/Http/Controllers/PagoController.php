<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index()
    {
        return response()->json(Pago::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['paciente_id' => 'required|exists:pacientes,id', 'monto' => 'required|numeric', 'metodo' => 'required|string']);
        return response()->json(Pago::create($data), 201);
    }
    public function show(Pago $pago)
    {
        return response()->json($pago, 200);
    }
    public function update(Request $r, Pago $pago)
    {
        $data = $r->validate(['monto' => 'sometimes|numeric', 'metodo' => 'sometimes|string']);
        $pago->update($data);
        return response()->json($pago, 200);
    }
    public function destroy(Pago $pago)
    {
        $pago->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
