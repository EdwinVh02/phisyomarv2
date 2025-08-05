<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COLUMNAS DE LA TABLA historial_medicos ===\n\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('historial_medicos');

foreach($columns as $column) {
    echo "- $column\n";
}

echo "\n=== TOTAL: " . count($columns) . " columnas ===\n";