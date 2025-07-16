<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(Bitacora::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['usuario_id' => 'required|exists:usuarios,id', 'accion' => 'required', 'tabla' => 'required']);
        return response()->json(Bitacora::create($data), 201);
    }
    public function show(Bitacora $b)
    {
        return response()->json($b, 200);
    }
    public function update(Request $r, Bitacora $b)
    {
        $data = $r->validate(['accion' => 'sometimes', 'tabla' => 'sometimes']);
        $b->update($data);
        return response()->json($b, 200);
    }
    public function destroy(Bitacora $b)
    {
        $b->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
