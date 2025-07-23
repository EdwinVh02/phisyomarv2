<?php

require_once 'vendor/autoload.php';

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Test the API endpoint directly
use App\Models\Usuario;

try {
    echo "=== PRUEBA DE AUTENTICACIÓN API ===\n\n";
    
    // Find a user and get their token
    $usuario = Usuario::first();
    if (!$usuario) {
        echo "❌ No hay usuarios en la base de datos\n";
        exit(1);
    }
    
    echo "✅ Usuario encontrado: {$usuario->nombre} ({$usuario->correo_electronico})\n";
    
    // Create a token
    $tokenResult = $usuario->createToken('Test Token');
    $token = $tokenResult->plainTextToken;
    echo "✅ Token creado: " . substr($token, 0, 30) . "...\n";
    echo "Token ID en DB: {$tokenResult->accessToken->id}\n";
    
    // Check if the token exists in database
    $dbToken = \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $usuario->id)
        ->where('tokenable_type', 'App\Models\Usuario')
        ->latest()->first();
    
    if ($dbToken) {
        echo "✅ Token encontrado en DB: ID {$dbToken->id}\n";
        echo "Hash en DB: " . substr($dbToken->token, 0, 20) . "...\n";
    } else {
        echo "❌ Token NO encontrado en DB\n";
    }
    echo "\n";
    
    // Test API call using Guzzle
    $client = new \GuzzleHttp\Client();
    
    // Try with different configurations
    echo "=== PRUEBA 1: Con middleware de SPA ===\n";
    $response = $client->get('http://localhost:8000/api/debug-headers', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]
    ]);
    
    $body = json_decode($response->getBody(), true);
    echo "Usuario encontrado: " . ($body['user'] ? "SI" : "NO") . "\n\n";
    
    // Try directly using Laravel's auth system
    echo "=== PRUEBA 2: Directamente con Laravel Auth ===\n";
    
    // Simulate the request that Sanctum would process
    $request = new \Illuminate\Http\Request();
    $request->headers->set('Authorization', 'Bearer ' . $token);
    $request->headers->set('Accept', 'application/json');
    
    // Set up auth for testing
    \Illuminate\Support\Facades\Auth::setDefaultDriver('sanctum');
    
    $authenticatedUser = \Illuminate\Support\Facades\Auth::guard('sanctum')->user();
    echo "Usuario autenticado directamente: " . ($authenticatedUser ? $authenticatedUser->nombre : "NINGUNO") . "\n\n";
    
    if ($body['user']) {
        echo "✅ Autenticación exitosa!\n";
        echo "Usuario autenticado: {$body['user']['nombre']}\n";
        
        // Now test the patients API
        echo "\n=== PROBANDO API DE PACIENTES ===\n";
        $patientsResponse = $client->get('http://localhost:8000/api/pacientes?all=true', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        $patients = json_decode($patientsResponse->getBody(), true);
        echo "✅ Pacientes obtenidos: " . count($patients) . "\n";
        if (count($patients) > 0) {
            echo "Primer paciente: {$patients[0]['usuario']['nombre']} {$patients[0]['usuario']['apellido_paterno']}\n";
        }
        
    } else {
        echo "❌ Autenticación falló\n";
        echo "Token enviado: " . $body['bearer_token'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}