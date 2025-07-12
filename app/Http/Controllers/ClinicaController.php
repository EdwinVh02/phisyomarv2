<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use Illuminate\Http\Request;

class ClinicaController extends Controller
{
    public function index()
    {
        return response()->json(Clinica::all(), 200);
    }
    public function store(Request $r)
    {
        $data = $r->validate(['nombre' => 'required']);
        return response()->json(Clinica::create($data), 201);
    }
    public function show(Clinica $clinica)
    {
        return response()->json($clinica, 200);
    }
    public function update(Request $r, Clinica $clinica)
    {
        $data = $r->validate(['nombre' => 'sometimes']);
        $clinica->update($data);
        return response()->json($clinica, 200);
    }
    public function destroy(Clinica $clinica)
    {
        $clinica->delete();
        return response()->json(['message' => 'Eliminado'], 200);
    }
}
