<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Historiales médicos en la base de datos:\n\n";

foreach(\App\Models\HistorialMedico::with('paciente.usuario')->get() as $h) {
    echo "ID: {$h->id}\n";
    echo "Paciente: " . ($h->paciente->usuario->nombre ?? 'Sin nombre') . "\n";
    echo "Motivo: " . substr($h->motivo_consulta ?? 'Sin motivo', 0, 80) . "\n";
    echo "Dolor EVA: {$h->dolor_eva}\n";
    echo "Fecha creación: {$h->fecha_creacion}\n";
    echo "Observaciones: " . substr($h->observacion_general ?? 'Sin observaciones', 0, 50) . "\n";
    echo "------------------------\n\n";
}