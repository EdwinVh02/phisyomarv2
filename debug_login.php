<?php

// Script de debug para Railway - verificar el estado del login

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\\Contracts\\Console\\Kernel');
$kernel->bootstrap();

echo "=== DEBUG LOGIN RAILWAY ===\n\n";

try {
    // 1. Verificar conexión a BD
    echo "1. Verificando conexión a base de datos...\n";
    $connection = \Illuminate\Support\Facades\DB::connection();
    $pdo = $connection->getPdo();
    echo "✓ Conexión a BD exitosa\n";
    echo "Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    
    // 2. Verificar tabla usuarios
    echo "\n2. Verificando tabla usuarios...\n";
    $usuariosCount = \App\Models\Usuario::count();
    echo "✓ Tabla usuarios accesible - Total usuarios: $usuariosCount\n";
    
    // 3. Verificar tabla roles
    echo "\n3. Verificando tabla roles...\n";
    $rolesCount = \App\Models\Rol::count();
    echo "✓ Tabla roles accesible - Total roles: $rolesCount\n";
    
    // 4. Mostrar algunos usuarios de prueba
    echo "\n4. Usuarios existentes:\n";
    $usuarios = \App\Models\Usuario::with('rol')->take(5)->get();
    foreach ($usuarios as $usuario) {
        echo "- ID: {$usuario->id}, Email: {$usuario->correo_electronico}, Rol: " . ($usuario->rol->nombre ?? 'N/A') . "\n";
    }
    
    // 5. Buscar terapeuta específico
    echo "\n5. Buscando terapeutas...\n";
    $terapeutas = \App\Models\Usuario::where('rol_id', 2)->with('rol')->get();
    echo "✓ Terapeutas encontrados: " . $terapeutas->count() . "\n";
    foreach ($terapeutas as $terapeuta) {
        echo "- Terapeuta: {$terapeuta->correo_electronico}\n";
    }
    
    // 6. Verificar tabla terapeutas
    echo "\n6. Verificando tabla terapeutas...\n";
    try {
        $terapeutasTable = \App\Models\Terapeuta::count();
        echo "✓ Tabla terapeutas accesible - Total: $terapeutasTable\n";
    } catch (\Exception $e) {
        echo "✗ Error en tabla terapeutas: " . $e->getMessage() . "\n";
    }
    
    // 7. Simular login con credenciales de terapeuta
    echo "\n7. Simulando proceso de login...\n";
    $testEmail = $terapeutas->first()?->correo_electronico;
    if ($testEmail) {
        echo "Probando con email: $testEmail\n";
        $usuario = \App\Models\Usuario::where('correo_electronico', $testEmail)->first();
        if ($usuario) {
            echo "✓ Usuario encontrado\n";
            
            // Verificar servicio UserRoleRegistrationService
            echo "Verificando UserRoleRegistrationService...\n";
            try {
                $result = \App\Services\UserRoleRegistrationService::createRoleSpecificRecord($usuario);
                echo "✓ UserRoleRegistrationService ejecutado: " . ($result ? 'true' : 'false') . "\n";
                
                $profileData = \App\Services\UserRoleRegistrationService::getUserProfileData($usuario);
                echo "✓ getUserProfileData ejecutado\n";
                echo "Role name: " . $profileData['role_name'] . "\n";
                echo "Profile complete: " . ($profileData['profile_complete'] ? 'true' : 'false') . "\n";
                
            } catch (\Exception $e) {
                echo "✗ Error en UserRoleRegistrationService: " . $e->getMessage() . "\n";
                echo "Stack trace: " . $e->getTraceAsString() . "\n";
            }
        }
    } else {
        echo "No hay terapeutas para probar\n";
    }
    
    // 8. Verificar configuración de timezone
    echo "\n8. Configuración del sistema:\n";
    echo "Timezone app: " . config('app.timezone') . "\n";
    echo "Timezone sistema: " . date_default_timezone_get() . "\n";
    echo "Fecha actual: " . now() . "\n";
    
    // 9. Verificar variables de entorno importantes
    echo "\n9. Variables de entorno:\n";
    echo "APP_ENV: " . config('app.env') . "\n";
    echo "APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";
    echo "DB_CONNECTION: " . config('database.default') . "\n";
    
} catch (\Exception $e) {
    echo "✗ ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DEBUG ===\n";