<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Models\Cita;
use Carbon\Carbon;

// Configurar fecha de prueba
$fecha = '2025-07-16';
$terapeutaId = 10;
$duracion = 60;

echo "=== PRUEBA DE DISPONIBILIDAD ===" . PHP_EOL;
echo "Fecha: $fecha" . PHP_EOL;
echo "Terapeuta ID: $terapeutaId" . PHP_EOL;
echo "Duración: $duracion minutos" . PHP_EOL;
echo "=================================" . PHP_EOL;

// Verificar si el terapeuta existe
$terapeuta = App\Models\Terapeuta::find($terapeutaId);
if (!$terapeuta) {
    echo "ERROR: Terapeuta no encontrado" . PHP_EOL;
    exit;
}

// Obtener citas del día
$citasDelDia = Cita::where('terapeuta_id', $terapeutaId)
    ->whereDate('fecha_hora', $fecha)
    ->where('estado', '!=', 'cancelada')
    ->get();

echo "Citas existentes del día:" . PHP_EOL;
foreach ($citasDelDia as $cita) {
    $inicio = Carbon::parse($cita->fecha_hora);
    $fin = $inicio->copy()->addMinutes($cita->duracion);
    echo "- {$cita->fecha_hora} ({$cita->tipo}, {$cita->duracion} min) -> {$inicio->format('H:i')} a {$fin->format('H:i')}" . PHP_EOL;
}
echo "=================================" . PHP_EOL;

// Verificar cada hora del día
echo "Verificando disponibilidad por hora:" . PHP_EOL;
for ($hora = 8; $hora < 18; $hora++) {
    $fechaHora = Carbon::parse($fecha)->hour($hora)->minute(0)->second(0);
    
    // Verificar si es en el pasado
    if ($fechaHora->isPast()) {
        echo "- {$fechaHora->format('H:i')}: NO DISPONIBLE (pasado)" . PHP_EOL;
        continue;
    }
    
    // Verificar disponibilidad
    $disponible = Cita::estaDisponible($fechaHora, $terapeutaId, $duracion);
    $status = $disponible ? "DISPONIBLE" : "OCUPADO";
    echo "- {$fechaHora->format('H:i')}: $status" . PHP_EOL;
    
    // Detalles de conflictos
    if (!$disponible) {
        $fechaInicio = $fechaHora;
        $fechaFin = $fechaInicio->copy()->addMinutes($duracion);
        echo "  → Conflicto: Nueva cita sería de {$fechaInicio->format('H:i')} a {$fechaFin->format('H:i')}" . PHP_EOL;
    }
}

echo "=================================" . PHP_EOL;

// Obtener horas disponibles usando el método del modelo
$horasDisponibles = Cita::horasDisponibles($fecha, $terapeutaId, $duracion);
echo "Horas disponibles finales: " . json_encode($horasDisponibles) . PHP_EOL;

echo "=================================" . PHP_EOL;
echo "Fecha actual: " . Carbon::now()->format('Y-m-d H:i:s') . PHP_EOL;
echo "Fecha de prueba: $fecha" . PHP_EOL;