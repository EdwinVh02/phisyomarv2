<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistroRequest;
use App\Http\Requests\UpdateRegistroRequest;
use App\Models\Registro;
use App\Models\Cita;
use App\Models\Paciente;
use Carbon\Carbon;
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

    /**
     * Obtener estadísticas del terapeuta autenticado
     */
    public function estadisticasTerapeuta(Request $request)
    {
        $user = $request->user();

        // Verificar que el usuario sea un terapeuta
        if ($user->rol_id !== 2) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $now = Carbon::now();
        $inicioMes = $now->startOfMonth()->copy();
        $finMes = $now->endOfMonth()->copy();
        $inicioSemana = $now->startOfWeek()->copy();
        $finSemana = $now->endOfWeek()->copy();

        // Estadísticas generales
        $totalCitas = Cita::where('terapeuta_id', $user->id)->count();
        $citasCompletadas = Cita::where('terapeuta_id', $user->id)
            ->where('estado', 'completada')->count();
        $citasCanceladas = Cita::where('terapeuta_id', $user->id)
            ->where('estado', 'cancelada')->count();
        
        $totalPacientes = Paciente::whereHas('citas', function ($query) use ($user) {
            $query->where('terapeuta_id', $user->id);
        })->count();

        // Citas de este mes
        $citasEsteMes = Cita::where('terapeuta_id', $user->id)
            ->whereBetween('fecha_hora', [$inicioMes, $finMes])
            ->count();

        // Citas de esta semana
        $citasEstaSemana = Cita::where('terapeuta_id', $user->id)
            ->whereBetween('fecha_hora', [$inicioSemana, $finSemana])
            ->count();

        // Citas de hoy
        $citasHoy = Cita::where('terapeuta_id', $user->id)
            ->whereDate('fecha_hora', $now->toDateString())
            ->count();

        // Próximas citas (próximos 7 días)
        $proximasCitas = Cita::where('terapeuta_id', $user->id)
            ->whereBetween('fecha_hora', [$now, $now->copy()->addDays(7)])
            ->where('estado', '!=', 'cancelada')
            ->count();

        // Estadísticas por día de la semana (últimas 4 semanas)
        $inicioUltimasSemanas = $now->copy()->subWeeks(4);
        $citasPorDia = [];
        for ($i = 0; $i < 7; $i++) {
            $dia = $inicioUltimasSemanas->copy()->addDays($i);
            $nombreDia = $dia->locale('es')->dayName;
            $citasPorDia[$nombreDia] = Cita::where('terapeuta_id', $user->id)
                ->whereBetween('fecha_hora', [$inicioUltimasSemanas, $now])
                ->whereRaw('DAYOFWEEK(fecha_hora) = ?', [$i + 1])
                ->count();
        }

        // Tipos de cita más comunes
        $tiposCita = Cita::where('terapeuta_id', $user->id)
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'resumen' => [
                'total_citas' => $totalCitas,
                'citas_completadas' => $citasCompletadas,
                'citas_canceladas' => $citasCanceladas,
                'total_pacientes' => $totalPacientes,
                'tasa_completado' => $totalCitas > 0 ? round(($citasCompletadas / $totalCitas) * 100, 2) : 0,
            ],
            'periodo_actual' => [
                'citas_hoy' => $citasHoy,
                'citas_esta_semana' => $citasEstaSemana,
                'citas_este_mes' => $citasEsteMes,
                'proximas_citas' => $proximasCitas,
            ],
            'citas_por_dia' => $citasPorDia,
            'tipos_cita' => $tiposCita,
        ], 200);
    }
}
