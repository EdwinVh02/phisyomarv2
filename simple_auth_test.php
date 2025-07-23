<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Usuario;

try {
    $usuario = Usuario::first();
    $token = $usuario->createToken('Fresh Token')->plainTextToken;
    echo "Token creado: {$token}\n\n";
    
    $client = new \GuzzleHttp\Client();
    
    // Test the unprotected debug endpoint
    $response = $client->get('http://localhost:8000/api/debug-headers', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]
    ]);
    
    $body = json_decode($response->getBody(), true);
    
    echo "=== DEBUG ENDPOINT (sin auth:sanctum) ===\n";
    echo "Usuario autenticado: " . ($body['user'] ? $body['user']['nombre'] : 'NINGUNO') . "\n";
    echo "Token recibido por el servidor: {$body['bearer_token']}\n\n";
    
    // Test a protected endpoint
    echo "=== ENDPOINT PROTEGIDO (con auth:sanctum) ===\n";
    try {
        $userResponse = $client->get('http://localhost:8000/api/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        $userData = json_decode($userResponse->getBody(), true);
        echo "✅ Autenticación exitosa en endpoint protegido!\n";
        echo "Usuario: {$userData['nombre']} ({$userData['correo_electronico']})\n";
        
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        echo "❌ Error en endpoint protegido: " . $e->getResponse()->getStatusCode() . "\n";
        echo "Mensaje: " . $e->getResponse()->getBody() . "\n";
    }
    
    // Test the patients API
    echo "\n=== API DE PACIENTES ===\n";
    try {
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
        
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        echo "❌ Error en API de pacientes: " . $e->getResponse()->getStatusCode() . "\n";
        echo "Mensaje: " . $e->getResponse()->getBody() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}