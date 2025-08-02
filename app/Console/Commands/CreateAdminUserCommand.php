<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Models\Administrador;
use App\Models\Rol;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateAdminUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:admin 
                           {email : Email del administrador}
                           {password : Contraseña del administrador}
                           {--name= : Nombre del administrador}
                           {--apellido_paterno= : Apellido paterno}
                           {--apellido_materno= : Apellido materno}';

    /**
     * The console command description.
     */
    protected $description = 'Crear un usuario administrador en el sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->option('name') ?? 'Admin';
        $apellidoPaterno = $this->option('apellido_paterno') ?? 'Sistema';
        $apellidoMaterno = $this->option('apellido_materno') ?? 'PhisyoMar';

        // Verificar si el usuario ya existe
        if (Usuario::where('correo_electronico', $email)->exists()) {
            $this->error("Ya existe un usuario con el email: {$email}");
            return 1;
        }

        // Verificar que existe el rol de administrador
        $adminRole = Rol::where('name', 'Administrador')->first();
        if (!$adminRole) {
            $this->error('No existe el rol de Administrador. Ejecuta primero: php artisan db:seed --class=RolesSeeder');
            return 1;
        }

        DB::beginTransaction();
        
        try {
            // Crear usuario administrador
            $usuario = Usuario::create([
                'nombre' => $name,
                'apellido_paterno' => $apellidoPaterno,
                'apellido_materno' => $apellidoMaterno,
                'correo_electronico' => $email,
                'contraseña' => $password, // Se hashea automáticamente por el mutador
                'telefono' => '0000000000', // Temporal
                'direccion' => 'Sistema',
                'fecha_nacimiento' => '1990-01-01',
                'sexo' => 'Otro',
                'curp' => strtoupper(substr(md5($email), 0, 18)), // CURP temporal única
                'ocupacion' => 'Administrador del Sistema',
                'estatus' => 'activo',
                'rol_id' => $adminRole->id
            ]);

            // Crear registro en tabla administradores
            Administrador::create([
                'id' => $usuario->id,
                'cedula_profesional' => null, // Opcional para admin
                'clinica_id' => null // Se puede asignar después
            ]);

            DB::commit();

            $this->info('✓ Usuario administrador creado exitosamente:');
            $this->line("  - ID: {$usuario->id}");
            $this->line("  - Nombre: {$usuario->nombre} {$usuario->apellido_paterno} {$usuario->apellido_materno}");
            $this->line("  - Email: {$usuario->correo_electronico}");
            $this->line("  - Rol: {$adminRole->name}");
            $this->line("  - Estado: {$usuario->estatus}");

            $this->info("\n🔑 Credenciales de acceso:");
            $this->line("  - Usuario: {$email}");
            $this->line("  - Contraseña: [PROTEGIDA]");

            $this->warn("\n⚠️  Importante: Cambia la contraseña después del primer login.");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error al crear el usuario administrador: " . $e->getMessage());
            return 1;
        }
    }
}