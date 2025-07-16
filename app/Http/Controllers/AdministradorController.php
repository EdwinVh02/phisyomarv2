<?php

namespace App\Http\Controllers;

use App\Models\Administrador;
use Illuminate\Http\Request;

class AdministradorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(Administrador::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['usuario_id' => 'required|exists:usuarios,id', 'area' => 'required|string']);
        return response()->json(Administrador::create($data), 201);
    }
    public function show(Administrador $a)
    {
        return response()->json($a, 200);
    }
    public function update(Request $r, Administrador $a)
    {
        $data = $r->validate(['area' => 'sometimes|string']);
        $a->update($data);
        return response()->json($a, 200);
    }
    public function destroy(Administrador $a)
    {
        $a->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
