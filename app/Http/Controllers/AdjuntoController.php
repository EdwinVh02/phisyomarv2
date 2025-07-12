<?php

namespace App\Http\Controllers;

use App\Models\Adjunto;
use Illuminate\Http\Request;

class AdjuntoController extends Controller
{
    /**
     * Listar todos los adjuntos.
     */
    public function index()
    {
        return response()->json(Adjunto::all(), 200);
    }

    /**
     * Crear un nuevo adjunto.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'registro_id' => 'required|exists:registros,id',
            'ruta'        => 'required|string|max:255',
        ]);

        $adjunto = Adjunto::create($validated);

        return response()->json($adjunto, 201);
    }

    /**
     * Mostrar un adjunto específico.
     */
    public function show(Adjunto $adjunto)
    {
        return response()->json($adjunto, 200);
    }

    /**
     * Actualizar un adjunto.
     */
    public function update(Request $request, Adjunto $adjunto)
    {
        $validated = $request->validate([
            'registro_id' => 'sometimes|exists:registros,id',
            'ruta'        => 'sometimes|string|max:255',
        ]);

        $adjunto->update($validated);

        return response()->json($adjunto, 200);
    }

    /**
     * Eliminar un adjunto.
     */
    public function destroy(Adjunto $adjunto)
    {
        $adjunto->delete();
        return response()->json(['message' => 'Adjunto eliminado'], 200);
    }
}
