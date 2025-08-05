<?php
require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tarifa;
use App\Models\Cita;
use App\Models\Usuario;
use App\Models\Paciente;
use App\Models\Terapeuta;
use App\Http\Controllers\CitaController;
use Illuminate\Http\Request;

try {
    echo "=== Configurando sistema de pagos ===\n";
    
    // 1. Crear tarifas si no existen
    echo "1. Creando tarifas...\n";
    
    $tarifas = [
        ['titulo' => 'Consulta General', 'precio' => 1000.00, 'tipo' => 'General', 'condiciones' => 'Consulta de fisioterapia general'],
        ['titulo' => 'Consulta Especializada', 'precio' => 1500.00, 'tipo' => 'Especializada', 'condiciones' => 'Consulta con especialista certificado'],
        ['titulo' => 'Consulta Reducida', 'precio' => 800.00, 'tipo' => 'Reducida', 'condiciones' => 'Tarifa para estudiantes y adultos mayores'],
    ];
    
    foreach ($tarifas as $tarifaData) {
        $tarifa = Tarifa::where('titulo', $tarifaData['titulo'])->first();
        if (!$tarifa) {
            Tarifa::create($tarifaData);
            echo "   ✓ Tarifa creada: {$tarifaData['titulo']} - \${$tarifaData['precio']}\n";
        } else {
            echo "   - Tarifa ya existe: {$tarifaData['titulo']}\n";
        }
    }
    
    // 2. Verificar citas existentes
    echo "\n2. Verificando citas existentes...\n";
    $citas = Cita::with(['paciente.usuario', 'terapeuta.usuario', 'pagoActual'])
                  ->orderBy('fecha_hora', 'desc')
                  ->limit(5)
                  ->get();
    
    echo "   Citas encontradas: " . $citas->count() . "\n";
    
    foreach ($citas as $cita) {
        $paciente = $cita->paciente->usuario ?? null;
        $terapeuta = $cita->terapeuta->usuario ?? null;
        $pagada = $cita->pagoActual ? 'Sí' : 'No';
        
        echo "   ID: {$cita->id} | Paciente: " . ($paciente ? $paciente->nombre : 'N/A') . 
             " | Terapeuta: " . ($terapeuta ? $terapeuta->nombre : 'N/A') . 
             " | Fecha: {$cita->fecha_hora} | Pagada: {$pagada}\n";
    }
    
    // 3. Probar API de citas para recepcionista
    echo "\n3. Probando API de citas para recepcionista...\n";
    
    // Buscar un usuario recepcionista o administrador
    $recepcionista = Usuario::where('rol_id', 3)->first() ?? Usuario::where('rol_id', 1)->first();
    
    if (!$recepcionista) {
        echo "   ✗ No se encontró usuario recepcionista o administrador\n";
        exit(1);
    }
    
    echo "   Usuario: {$recepcionista->correo_electronico} (Rol: {$recepcionista->rol_id})\n";
    
    // Simular request
    $request = new Request();
    $request->setUserResolver(function () use ($recepcionista) {
        return $recepcionista;
    });
    
    $controller = new CitaController();
    $response = $controller->citasRecepcionista($request);
    
    echo "   Respuesta HTTP: " . $response->getStatusCode() . "\n";
    $content = json_decode($response->getContent(), true);
    
    if ($content && $content['success']) {
        echo "   ✓ API funcionando correctamente\n";
        echo "   Total de citas: " . count($content['data']) . "\n";
        
        foreach ($content['data'] as $index => $cita) {
            if ($index < 3) { // Mostrar solo las primeras 3
                echo "     - Cita #{$cita['id']}: Costo: \${$cita['costo_consulta']}, Pagada: " . ($cita['pagada'] ? 'Sí' : 'No') . "\n";
            }
        }
    } else {
        echo "   ✗ Error en API: " . ($content['message'] ?? 'Error desconocido') . "\n";
    }
    
    // 4. Probar API de tarifas
    echo "\n4. Probando API de tarifas...\n";
    $tarifasResponse = $controller->obtenerTarifas();
    $tarifasContent = json_decode($tarifasResponse->getContent(), true);
    
    if ($tarifasContent && $tarifasContent['success']) {
        echo "   ✓ API de tarifas funcionando\n";
        echo "   Tarifas disponibles: " . count($tarifasContent['data']) . "\n";
        foreach ($tarifasContent['data'] as $tarifa) {
            echo "     - {$tarifa['titulo']}: \${$tarifa['precio']} ({$tarifa['tipo']})\n";
        }
    } else {
        echo "   ✗ Error en API de tarifas\n";
    }
    
    // 5. Crear una cita de prueba si no hay ninguna
    if ($citas->count() === 0) {
        echo "\n5. Creando cita de prueba...\n";
        
        $paciente = Paciente::with('usuario')->first();
        $terapeuta = Terapeuta::with('usuario')->first();
        
        if ($paciente && $terapeuta) {
            $cita = Cita::create([
                'fecha_hora' => now()->addDays(1)->setHour(10)->setMinute(0),
                'tipo' => 'Consulta General',
                'duracion' => 60,
                'motivo' => 'Consulta de prueba para sistema de pagos',
                'estado' => 'agendada',
                'paciente_id' => $paciente->id,
                'terapeuta_id' => $terapeuta->id,
            ]);
            
            echo "   ✓ Cita de prueba creada: ID {$cita->id}\n";
            echo "     Paciente: {$paciente->usuario->nombre}\n";
            echo "     Terapeuta: {$terapeuta->usuario->nombre}\n";
            echo "     Fecha: {$cita->fecha_hora}\n";
        } else {
            echo "   ✗ No se encontraron pacientes o terapeutas para crear cita de prueba\n";
        }
    }
    
    echo "\n✅ Sistema de pagos configurado y probado exitosamente\n";
    echo "\nPuedes acceder a:\n";
    echo "- Frontend: http://localhost:5173/recepcionista/citas\n";
    echo "- API citas: GET /api/recepcionista/citas\n";
    echo "- API pago: POST /api/recepcionista/citas/{id}/pago\n";
    echo "- API tarifas: GET /api/recepcionista/tarifas\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}