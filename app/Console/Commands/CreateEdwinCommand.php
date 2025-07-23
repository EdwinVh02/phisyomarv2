<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use App\Models\Paciente;
use Illuminate\Support\Facades\Hash;

class CreateEdwinCommand extends Command
{
    protected $signature = 'create:edwin';
    protected $description = 'Crear usuario Edwin como paciente';

    public function handle()
    {
        $this->info('Creando usuario Edwin...');
        
        // Verificar si ya existe
        $existingUser = Usuario::where('correo_electronico', 'edwin@phisyomar.com')->first();
        
        if ($existingUser) {
            $this->info('Usuario Edwin ya existe');
            return 0;
        }
        
        // Crear usuario Edwin
        $usuario = new Usuario();
        $usuario->nombre = 'Edwin';
        $usuario->apellido_paterno = 'Vazquez';
        $usuario->apellido_materno = 'Hernandez';
        $usuario->correo_electronico = 'edwin@phisyomar.com';
        $usuario->contraseña = Hash::make('Password123!');
        $usuario->rol_id = 4; // Paciente
        $usuario->telefono = '5551234567';
        $usuario->fecha_nacimiento = '1990-01-01';
        $usuario->sexo = 'Masculino';
        $usuario->curp = 'VAHE900101HDFZRD01';
        $usuario->save();
        
        $this->info("✅ Usuario Edwin creado con ID: {$usuario->id}");
        
        // Crear registro de paciente
        $paciente = new Paciente();
        $paciente->id = $usuario->id;
        $paciente->contacto_emergencia_nombre = 'Contacto de Edwin';
        $paciente->contacto_emergencia_telefono = '5551234567';
        $paciente->contacto_emergencia_parentesco = 'Familiar';
        $paciente->save();
        
        $this->info("✅ Registro de paciente creado para Edwin");
        
        $this->info('Proceso completado!');
        $this->info("Email: edwin@phisyomar.com");
        $this->info("Password: Password123!");
        
        return 0;
    }
}