<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;

class ListUsersCommand extends Command
{
    protected $signature = 'list:users';
    protected $description = 'Listar todos los usuarios';

    public function handle()
    {
        $usuarios = Usuario::all();
        
        $this->info("Total de usuarios: {$usuarios->count()}");
        $this->newLine();
        
        foreach ($usuarios as $usuario) {
            $rolName = match($usuario->rol_id) {
                1 => 'Administrador',
                2 => 'Terapeuta',
                3 => 'Recepcionista',
                4 => 'Paciente',
                default => 'Desconocido'
            };
            
            $this->info("ID: {$usuario->id} | Nombre: {$usuario->nombre} | Email: {$usuario->correo_electronico} | Rol: {$rolName}");
        }
        
        return 0;
    }
}