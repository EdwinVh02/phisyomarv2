<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Probando creación automática de historial médico desde cita...\n\n";

try {
    // Crear una cita de prueba
    $cita = new \App\Models\Cita();
    $cita->paciente_id = 2; // Paciente edwin
    $cita->motivo = 'Test de dolor de cuello por estrés laboral';
    $cita->escala_dolor_eva_inicio = 6;
    $cita->observaciones = 'Paciente con molestias cervicales y rigidez matutina';
    $cita->como_fue_lesion = 'Dolor progresivo por malas posturas en trabajo de oficina';
    $cita->antecedentes_patologicos = 'Hipertensión controlada con medicamento';
    $cita->fecha_hora = now();
    $cita->tipo = 'Consulta';
    $cita->estado = 'agendada';
    $cita->terapeuta_id = 3; // Terapeuta Jose
    $cita->duracion = 60;
    $cita->save();
    
    echo "✓ Cita creada con ID: {$cita->id}\n";
    
    // Probar la creación automática del historial
    $controller = new \App\Http\Controllers\HistorialMedicoController();
    $historial = $controller->crearOActualizarDesdeCita($cita);
    
    if ($historial) {
        echo "✓ Historial médico creado/actualizado exitosamente\n";
        echo "  - ID del historial: {$historial->id}\n";
        echo "  - Paciente ID: {$historial->paciente_id}\n";
        echo "  - Motivo: {$historial->motivo_consulta}\n";
        echo "  - Dolor EVA: {$historial->dolor_eva}\n";
        echo "  - Lesiones previas: " . ($historial->lesiones_previas ?: 'N/A') . "\n";
        echo "  - Antecedentes: " . ($historial->antecedentes_familiares ?: 'N/A') . "\n";
    } else {
        echo "✗ Error: No se pudo crear el historial médico\n";
    }
    
    // Verificar que el historial existe en la base de datos
    $historialDB = \App\Models\HistorialMedico::where('paciente_id', 2)->first();
    if ($historialDB) {
        echo "\n✓ Verificación en BD: Historial encontrado\n";
        echo "  - Fecha creación: {$historialDB->fecha_creacion}\n";
        echo "  - Observación general: " . ($historialDB->observacion_general ?: 'N/A') . "\n";
    }
    
    // Simular completar la cita y agregar evolución
    $cita->estado = 'completada';
    $cita->escala_dolor_eva_fin = 3;
    $cita->observaciones = $cita->observaciones . "\n\nSesión completada: Mejoría notable con terapia manual";
    $cita->save();
    
    $historialActualizado = $controller->actualizarDespuesCita($cita);
    if ($historialActualizado) {
        echo "\n✓ Historial actualizado después de completar cita\n";
        echo "  - Evolución agregada con dolor inicial: 6/10, final: 3/10\n";
    }
    
    echo "\n=== PRUEBA COMPLETADA EXITOSAMENTE ===\n";
    
} catch (\Exception $e) {
    echo "✗ Error durante la prueba: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}