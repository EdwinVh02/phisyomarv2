<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Terapeutas existentes en la base de datos:\n\n";

$terapeutas = \App\Models\Terapeuta::with('usuario')->get();

foreach ($terapeutas as $terapeuta) {
    echo "ID: {$terapeuta->id} - {$terapeuta->usuario->nombre} {$terapeuta->usuario->apellido_paterno}\n";
}

if ($terapeutas->isEmpty()) {
    echo "No hay terapeutas en la base de datos.\n";
}