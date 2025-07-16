<?php

namespace App\Http\Controllers;

use App\Models\Recepcionista;
use Illuminate\Http\Request;

class RecepcionistaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(Recepcionista::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['usuario_id' => 'required|exists:usuarios,id']);
        return response()->json(Recepcionista::create($data), 201);
    }
    public function show(Recepcionista $recepcionista)
    {
        return response()->json($recepcionista, 200);
    }
    public function update(Request $r, Recepcionista $recepcionista)
    {
        $data = $r->validate([]);
        $recepcionista->update($data);
        return response()->json($recepcionista, 200);
    }
    public function destroy(Recepcionista $recepcionista)
    {
        $recepcionista->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
