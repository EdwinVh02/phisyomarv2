<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use App\Models\Paciente;

class FixPacienteCommand extends Command
{
    protected $signature = 'fix:paciente';
    protected $description = 'Crear registro de paciente para usuarios con rol 4';

    public function handle()
    {
        $this->info('Verificando usuarios con rol de paciente...');
        
        // Buscar usuarios con rol de paciente (4)
        $usuarios = Usuario::where('rol_id', 4)->get();
        
        $this->info("Encontrados {$usuarios->count()} usuarios con rol de paciente");
        
        foreach ($usuarios as $usuario) {
            $this->info("Verificando usuario: {$usuario->nombre} (ID: {$usuario->id})");
            
            // Verificar si ya tiene registro de paciente
            $paciente = Paciente::find($usuario->id);
            
            if ($paciente) {
                $this->info("✅ Ya tiene registro de paciente");
            } else {
                $this->info("❌ No tiene registro de paciente. Creando...");
                
                // Crear registro de paciente
                $paciente = new Paciente();
                $paciente->id = $usuario->id;
                $paciente->contacto_emergencia_nombre = 'Contacto de ' . $usuario->nombre;
                $paciente->contacto_emergencia_telefono = '5551234567';
                $paciente->contacto_emergencia_parentesco = 'Familiar';
                $paciente->save();
                
                $this->info("✅ Registro de paciente creado exitosamente");
            }
        }
        
        $this->info('Proceso completado!');
        return 0;
    }
}