<?php

namespace App\Http\Controllers;

use App\Models\Atiende;
use Illuminate\Http\Request;

class AtiendeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(Atiende::all(), 200);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'cita_id' => 'required|exists:citas,id',
            'tratamiento_id' => 'required|exists:tratamientos,id',
        ]);

        return response()->json(Atiende::create($data), 201);
    }

    public function show(Atiende $atiende)
    {
        return response()->json($atiende, 200);
    }

    public function update(Request $r, Atiende $atiende)
    {
        $data = $r->validate([]);
        $atiende->update($data);

        return response()->json($atiende, 200);
    }

    public function destroy(Atiende $atiende)
    {
        $atiende->delete();

        return response()->json(['message' => 'Eliminado'], 200);
    }
}
