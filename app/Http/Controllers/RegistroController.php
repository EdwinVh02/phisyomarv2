<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use App\Http\Requests\StoreRegistroRequest;
use App\Http\Requests\UpdateRegistroRequest;
use Illuminate\Http\Request;

class RegistroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(Registro::all(), 200);
    }

    public function store(StoreRegistroRequest $request)
    {
        $data = $request->validated();
        return response()->json(Registro::create($data), 201);
    }

    public function show(Registro $registro)
    {
        return response()->json($registro, 200);
    }

    public function update(UpdateRegistroRequest $request, Registro $registro)
    {
        $data = $request->validated();
        $registro->update($data);
        return response()->json($registro, 200);
    }

    public function destroy(Registro $registro)
    {
        $registro->delete();
        return response()->json(['message' => 'Registro eliminado correctamente'], 200);
    }
}
