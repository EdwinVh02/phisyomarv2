<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Usuario;

// Usar el primer usuario con rol admin que exista
$usuario = Usuario::where('rol_id', 1)->first();

if (!$usuario) {
    echo "No hay usuarios admin en la base de datos\n";
    echo "Usuarios disponibles:\n";
    $usuarios = Usuario::all();
    foreach ($usuarios as $u) {
        echo "- ID: {$u->id}, Email: {$u->correo_electronico}, Rol: {$u->rol_id}\n";
    }
    $usuario = $usuarios->first(); // Usar el primer usuario disponible
}

echo "Usando usuario: {$usuario->correo_electronico} (Rol: {$usuario->rol_id})\n";

$token = $usuario->createToken('Test Token')->plainTextToken;
echo "Token: $token\n";
echo "User ID: {$usuario->id}\n";
echo "Email: {$usuario->correo_electronico}\n";
?>