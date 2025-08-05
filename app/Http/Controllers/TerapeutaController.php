<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTerapeutaRequest;
use App\Http\Requests\UpdateTerapeutaRequest;
use App\Models\Terapeuta;

class TerapeutaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        $terapeutas = Terapeuta::with(['usuario', 'especialidades'])
                                ->orderBy('created_at', 'desc')
                                ->get();
        
        return response()->json([
            'success' => true,
            'data' => $terapeutas
        ], 200);
    }

    public function store(StoreTerapeutaRequest $request)
    {
        $data = $request->validated();

        return response()->json(Terapeuta::create($data), 201);
    }

    public function show(Terapeuta $terapeuta)
    {
        // Cargar las relaciones necesarias (sin experiencias por ahora)
        $terapeuta->load(['usuario', 'especialidades']);
        
        return response()->json([
            'success' => true,
            'data' => $terapeuta
        ], 200);
    }

    public function update(UpdateTerapeutaRequest $request, Terapeuta $terapeuta)
    {
        $data = $request->validated();
        $terapeuta->update($data);

        return response()->json($terapeuta, 200);
    }

    public function destroy(Terapeuta $terapeuta)
    {
        $terapeuta->delete();

        return response()->json(['message' => 'Eliminado'], 200);
    }
}
