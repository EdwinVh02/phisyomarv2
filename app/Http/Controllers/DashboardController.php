<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obtener estadísticas completas del dashboard
     */
    public function getStats(Request $request)
    {
        try {
            $today = Carbon::now()->format('Y-m-d');
            $currentMonth = Carbon::now()->format('Y-m');

            // Conteos básicos
            $totalUsuarios = Usuario::count();
            $totalCitas = Cita::count();
            $citasHoy = Cita::whereDate('fecha_hora', $today)->count();
            
            // Ingresos del mes
            $ingresosMes = Pago::whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$currentMonth])
                              ->sum('monto') ?? 0;

            // Resumen de citas por estado
            $citasHoyPorEstado = Cita::whereDate('fecha_hora', $today)
                ->select('estado', DB::raw('COUNT(*) as count'))
                ->groupBy('estado')
                ->pluck('count', 'estado')
                ->toArray();

            // Actividad reciente (últimas 10 entradas de bitácora)
            $actividadReciente = Bitacora::with('usuario')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($bitacora) {
                    return [
                        'id' => $bitacora->id,
                        'accion' => $bitacora->descripcion ?? $bitacora->accion ?? 'Actividad del sistema',
                        'usuario' => $bitacora->usuario->nombre ?? 'Sistema',
                        'created_at' => $bitacora->created_at,
                        'tipo' => $this->determinarTipoActividad($bitacora->accion ?? '')
                    ];
                });

            // Calcular eficiencia (porcentaje de citas completadas)
            $citasCompletadas = Cita::where('estado', 'completada')->count();
            $eficiencia = $totalCitas > 0 ? ($citasCompletadas / $totalCitas) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'conteos' => [
                        'total_usuarios' => $totalUsuarios,
                        'total_citas' => $totalCitas,
                        'citas_hoy' => $citasHoy,
                        'ingresos_mes' => $ingresosMes,
                        'eficiencia' => round($eficiencia, 1)
                    ],
                    'citas_hoy_por_estado' => [
                        'programadas' => $citasHoyPorEstado['agendada'] ?? 0,
                        'completadas' => $citasHoyPorEstado['completada'] ?? 0,
                        'pendientes' => $citasHoyPorEstado['pendiente'] ?? 0,
                        'canceladas' => $citasHoyPorEstado['cancelada'] ?? 0
                    ],
                    'actividad_reciente' => $actividadReciente,
                    'fecha_actualizacion' => now()->toISOString()
                ],
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener conteos básicos para verificación
     */
    public function getBasicCounts()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'usuarios' => Usuario::count(),
                    'citas' => Cita::count(),
                    'pagos' => Pago::count(),
                    'bitacoras' => Bitacora::count(),
                    'usuarios_por_rol' => Usuario::select('rol_id', DB::raw('COUNT(*) as count'))
                        ->groupBy('rol_id')
                        ->with('rol')
                        ->get()
                        ->mapWithKeys(function ($item) {
                            return [$item->rol->name ?? "Rol {$item->rol_id}" => $item->count];
                        }),
                    'fecha_consulta' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conteos: ' . $e->getMessage(),
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Determinar el tipo de actividad para coloreo
     */
    private function determinarTipoActividad($accion)
    {
        $accion = strtolower($accion);
        
        if (str_contains($accion, 'cita')) return 'appointment';
        if (str_contains($accion, 'usuario') || str_contains($accion, 'registro')) return 'user';
        if (str_contains($accion, 'pago')) return 'payment';
        if (str_contains($accion, 'login') || str_contains($accion, 'logout')) return 'auth';
        
        return 'system';
    }
}