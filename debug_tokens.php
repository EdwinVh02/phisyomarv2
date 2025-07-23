<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Usuario;
use Laravel\Sanctum\PersonalAccessToken;

try {
    echo "=== DIAGNÓSTICO COMPLETO DE TOKENS ===\n\n";
    
    // Get the latest token
    $latestToken = PersonalAccessToken::latest()->first();
    
    if (!$latestToken) {
        echo "❌ No hay tokens en la base de datos\n";
        exit(1);
    }
    
    echo "✅ Token más reciente encontrado:\n";
    echo "  ID: {$latestToken->id}\n";
    echo "  Tokenable ID: {$latestToken->tokenable_id}\n";
    echo "  Tokenable Type: {$latestToken->tokenable_type}\n";
    echo "  Name: {$latestToken->name}\n";
    echo "  Token Hash: " . substr($latestToken->token, 0, 20) . "...\n";
    
    // Test the tokenable relationship
    echo "\n=== VERIFICANDO RELACIÓN TOKENABLE ===\n";
    $tokenableUser = $latestToken->tokenable;
    
    if ($tokenableUser) {
        echo "✅ Relación tokenable funciona:\n";
        echo "  Usuario: {$tokenableUser->nombre}\n";
        echo "  Email: {$tokenableUser->correo_electronico}\n";
        echo "  Clase: " . get_class($tokenableUser) . "\n";
    } else {
        echo "❌ Relación tokenable NO funciona\n";
        
        // Let's check if the user exists directly
        $directUser = Usuario::find($latestToken->tokenable_id);
        if ($directUser) {
            echo "  Pero el usuario SÍ existe en la BD:\n";
            echo "    ID: {$directUser->id}\n";
            echo "    Nombre: {$directUser->nombre}\n";
            echo "    Clase: " . get_class($directUser) . "\n";
        } else {
            echo "  Y el usuario NO existe en la BD\n";
        }
    }
    
    // Test manual token verification
    echo "\n=== VERIFICACIÓN MANUAL DE TOKEN ===\n";
    
    // Let's get the actual token from when it was created
    $usuario = Usuario::first();
    $tokenResult = $usuario->createToken('Debug Token');
    $actualPlainTextToken = $tokenResult->plainTextToken;
    $actualAccessToken = $tokenResult->accessToken;
    
    echo "Token plain text real: {$actualPlainTextToken}\n";
    echo "Token ID: {$actualAccessToken->id}\n";
    
    // Extract the part after the pipe
    list($tokenId, $tokenValue) = explode('|', $actualPlainTextToken, 2);
    echo "Token ID extraído: {$tokenId}\n";
    echo "Token value extraído: " . substr($tokenValue, 0, 20) . "...\n";
    
    // Test hash verification
    $testHash = hash('sha256', $tokenValue);
    echo "Hash calculado: " . substr($testHash, 0, 20) . "...\n";
    echo "Hash en BD:     " . substr($actualAccessToken->token, 0, 20) . "...\n";
    echo "Hashes coinciden: " . ($testHash === $actualAccessToken->token ? "SÍ" : "NO") . "\n";
    
    // Test Sanctum's findToken method
    echo "\n=== PROBANDO MÉTODO FINDTOKEN DE SANCTUM ===\n";
    $foundToken = PersonalAccessToken::findToken($actualPlainTextToken);
    
    if ($foundToken) {
        echo "✅ findToken encontró el token\n";
        echo "  ID encontrado: {$foundToken->id}\n";
        $foundUser = $foundToken->tokenable;
        echo "  Usuario asociado: " . ($foundUser ? $foundUser->nombre : "NINGUNO") . "\n";
    } else {
        echo "❌ findToken NO encontró el token\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}