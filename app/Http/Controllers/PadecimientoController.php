<?php

namespace App\Http\Controllers;

use App\Models\Padecimiento;
use Illuminate\Http\Request;

class PadecimientoController extends Controller
{
    public function index()
    {
        return response()->json(Padecimiento::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['nombre' => 'required|string']);
        return response()->json(Padecimiento::create($data), 201);
    }
    public function show(Padecimiento $padecimiento)
    {
        return response()->json($padecimiento, 200);
    }
    public function update(Request $r, Padecimiento $padecimiento)
    {
        $data = $r->validate(['nombre' => 'sometimes|string']);
        $padecimiento->update($data);
        return response()->json($padecimiento, 200);
    }
    public function destroy(Padecimiento $padecimiento)
    {
        $padecimiento->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
