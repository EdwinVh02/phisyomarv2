<?php

namespace App\Http\Controllers;

use App\Models\Smartwatch;
use Illuminate\Http\Request;

class SmartwatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(Smartwatch::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['paciente_id' => 'required|exists:pacientes,id', 'serial' => 'required|string']);
        return response()->json(Smartwatch::create($data), 201);
    }
    public function show(Smartwatch $smartwatch)
    {
        return response()->json($smartwatch, 200);
    }
    public function update(Request $r, Smartwatch $smartwatch)
    {
        $data = $r->validate(['serial' => 'sometimes|string']);
        $smartwatch->update($data);
        return response()->json($smartwatch, 200);
    }
    public function destroy(Smartwatch $smartwatch)
    {
        $smartwatch->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
