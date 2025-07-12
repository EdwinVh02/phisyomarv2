<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;

class RegistroController extends Controller
{
    public function index()
    {
        return response()->json(Registro::all(), 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'historial_medico_id'            => 'required|exists:historial_medicos,id',
            'fecha_hora'                     => 'required|date',
            'antecedentes'                   => 'nullable|string',
            'medicacion_actual'              => 'nullable|string',
            'postura'                        => 'nullable|string',
            'marcha'                         => 'nullable|string',
            'fuerza_muscular'                => 'nullable|string',
            'rango_movimiento_muscular_rom'  => 'nullable|string',
            'tono_muscular'                  => 'nullable|string',
            'localizacion_dolor'             => 'nullable|string',
            'intensidad_dolor'               => 'nullable|integer|min:0|max:10',
            'tipo_dolor'                     => 'nullable|string|max:100',
            'movilidad_articular'            => 'nullable|string',
            'balance_y_coordinacion'         => 'nullable|string',
            'sensibilidad'                   => 'nullable|string',
            'reflejos_osteotendinosos'       => 'nullable|string',
            'motivo_visita'                  => 'nullable|string',
            'numero_sesion'                  => 'nullable|integer',
            // Agrega más campos aquí si tu migración los tiene
        ]);
        return response()->json(Registro::create($data), 201);
    }

    public function show(Registro $registro)
    {
        return response()->json($registro, 200);
    }

    public function update(Request $request, Registro $registro)
    {
        $data = $request->validate([
            'fecha_hora'                     => 'sometimes|date',
            'antecedentes'                   => 'nullable|string',
            'medicacion_actual'              => 'nullable|string',
            'postura'                        => 'nullable|string',
            'marcha'                         => 'nullable|string',
            'fuerza_muscular'                => 'nullable|string',
            'rango_movimiento_muscular_rom'  => 'nullable|string',
            'tono_muscular'                  => 'nullable|string',
            'localizacion_dolor'             => 'nullable|string',
            'intensidad_dolor'               => 'nullable|integer|min:0|max:10',
            'tipo_dolor'                     => 'nullable|string|max:100',
            'movilidad_articular'            => 'nullable|string',
            'balance_y_coordinacion'         => 'nullable|string',
            'sensibilidad'                   => 'nullable|string',
            'reflejos_osteotendinosos'       => 'nullable|string',
            'motivo_visita'                  => 'nullable|string',
            'numero_sesion'                  => 'nullable|integer',
            // Agrega más campos aquí si tu migración los tiene
        ]);
        $registro->update($data);
        return response()->json($registro, 200);
    }

    public function destroy(Registro $registro)
    {
        $registro->delete();
        return response()->json(['message' => 'Registro eliminado correctamente'], 200);
    }
}
