<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Cita;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        return response()->json(Pago::all(), 200);
    }

    public function store(Request $r)
    {
        $data = $r->validate(['paciente_id' => 'required|exists:pacientes,id', 'monto' => 'required|numeric', 'metodo' => 'required|string']);

        return response()->json(Pago::create($data), 201);
    }

    public function show(Pago $pago)
    {
        return response()->json($pago, 200);
    }

    public function update(Request $r, Pago $pago)
    {
        $data = $r->validate(['monto' => 'sometimes|numeric', 'metodo' => 'sometimes|string']);
        $pago->update($data);

        return response()->json($pago, 200);
    }

    public function destroy(Pago $pago)
    {
        $pago->delete();

        return response()->json(['message' => 'Eliminado'], 200);
    }

    public function misPagos()
    {
        $user = auth()->user();
        
        // Verificar que el usuario es un paciente
        if ($user->rol_id !== 4) {
            return response()->json(['error' => 'Acceso denegado'], 403);
        }

        // Obtener el paciente
        $paciente = $user->paciente;
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }

        // Obtener los pagos del paciente a través de sus citas
        $pagos = Pago::whereHas('cita', function($query) use ($paciente) {
            $query->where('paciente_id', $paciente->id);
        })
        ->with(['cita.terapeuta.usuario', 'cita.tratamiento'])
        ->orderBy('fecha_hora', 'desc')
        ->get();

        // Formatear los pagos para el frontend
        $pagosFormateados = $pagos->map(function ($pago) {
            $numeroFactura = 'F-' . date('Y') . '-' . str_pad($pago->id, 3, '0', STR_PAD_LEFT);
            $fechaVencimiento = $pago->fecha_hora->copy()->addDays(30)->format('Y-m-d');
            
            return [
                'id' => $pago->id,
                'fecha' => $pago->fecha_hora->format('Y-m-d'),
                'concepto' => $pago->cita->tratamiento->nombre ?? 'Consulta de Fisioterapia',
                'monto' => $pago->monto,
                'estado' => $pago->factura_emitida ? 'pagado' : 'pendiente',
                'metodoPago' => $this->formatearMetodoPago($pago->forma_pago),
                'numeroFactura' => $numeroFactura,
                'terapeuta' => $pago->cita->terapeuta->usuario->nombre . ' ' . $pago->cita->terapeuta->usuario->apellido_paterno,
                'sesion' => $pago->cita->observaciones ?? 'Sesión de terapia',
                'vencimiento' => $fechaVencimiento,
                'recibo' => $pago->recibo,
                'autorizacion' => $pago->autorizacion,
            ];
        });

        // Calcular totales
        $totalPagado = $pagos->where('factura_emitida', true)->sum('monto');
        $totalPendiente = $pagos->where('factura_emitida', false)->sum('monto');
        $totalGeneral = $pagos->sum('monto');

        return response()->json([
            'pagos' => $pagosFormateados,
            'resumen' => [
                'total_pagado' => $totalPagado,
                'total_pendiente' => $totalPendiente,
                'total_general' => $totalGeneral,
                'total_facturas' => $pagos->count(),
            ]
        ], 200);
    }

    private function formatearMetodoPago($formaPago)
    {
        switch ($formaPago) {
            case 'efectivo':
                return 'Efectivo';
            case 'transferencia':
                return 'Transferencia Bancaria';
            case 'terminal':
                return 'Tarjeta de Crédito/Débito';
            default:
                return ucfirst($formaPago);
        }
    }
}
