<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Pacientes existentes en la base de datos:\n\n";

$pacientes = \App\Models\Paciente::with('usuario')->get();

foreach ($pacientes as $paciente) {
    echo "ID: {$paciente->id} - {$paciente->usuario->nombre} {$paciente->usuario->apellido_paterno}\n";
}

if ($pacientes->isEmpty()) {
    echo "No hay pacientes en la base de datos.\n";
}