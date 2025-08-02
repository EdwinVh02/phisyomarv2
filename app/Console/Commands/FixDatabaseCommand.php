<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Usuario;
use App\Models\Rol;

class FixDatabaseCommand extends Command
{
    protected $signature = 'fix:database';
    protected $description = 'Arregla problemas de la base de datos y verifica la integridad';

    public function handle()
    {
        $this->info('🔧 Iniciando verificación y arreglo de la base de datos...');

        // 1. Verificar y arreglar nombres de tablas
        $this->fixTableNames();

        // 2. Verificar roles
        $this->checkRoles();

        // 3. Verificar usuario administrador
        $this->checkAdminUser();

        // 4. Verificar integridad de datos
        $this->checkDataIntegrity();

        $this->info('✅ Verificación completada.');
        return 0;
    }

    private function fixTableNames()
    {
        $this->info('📋 Verificando nombres de tablas...');

        $tablesToFix = [
            'administradors' => 'administradores',
            'rols' => 'roles',
            'especialidads' => 'especialidades',
            'valoracions' => 'valoraciones'
        ];

        foreach ($tablesToFix as $oldName => $newName) {
            if (Schema::hasTable($oldName) && !Schema::hasTable($newName)) {
                $this->info("  Renombrando tabla {$oldName} -> {$newName}");
                Schema::rename($oldName, $newName);
            } elseif (Schema::hasTable($newName)) {
                $this->line("  ✓ Tabla {$newName} ya existe");
            } else {
                $this->warn("  ⚠️  Tabla {$oldName} no encontrada");
            }
        }
    }

    private function checkRoles()
    {
        $this->info('🔐 Verificando roles...');

        $rolesEsperados = [
            ['id' => 1, 'name' => 'Administrador'],
            ['id' => 2, 'name' => 'Terapeuta'],
            ['id' => 3, 'name' => 'Recepcionista'],
            ['id' => 4, 'name' => 'Paciente'],
        ];

        $tableName = Schema::hasTable('roles') ? 'roles' : 'rols';

        foreach ($rolesEsperados as $rol) {
            $exists = DB::table($tableName)->where('id', $rol['id'])->exists();
            if (!$exists) {
                DB::table($tableName)->insert($rol);
                $this->info("  ✓ Rol '{$rol['name']}' creado");
            } else {
                $this->line("  ✓ Rol '{$rol['name']}' ya existe");
            }
        }

        $totalRoles = DB::table($tableName)->count();
        $this->info("  Total de roles: {$totalRoles}");
    }

    private function checkAdminUser()
    {
        $this->info('👤 Verificando usuario administrador...');

        $adminUser = Usuario::where('correo_electronico', 'admin@phisyomar.com')->first();

        if (!$adminUser) {
            $this->info('  Creando usuario administrador...');
            
            $adminUser = Usuario::create([
                'nombre' => 'Admin',
                'apellido_paterno' => 'Sistema',
                'apellido_materno' => 'PhisyoMar',
                'correo_electronico' => 'admin@phisyomar.com',
                'contraseña' => 'admin123456', // Se hashea automáticamente
                'telefono' => '0000000000',
                'direccion' => 'Sistema',
                'fecha_nacimiento' => '1990-01-01',
                'sexo' => 'Otro',
                'curp' => 'ADMI900101HDFXXX01',
                'ocupacion' => 'Administrador del Sistema',
                'estatus' => 'activo',
                'rol_id' => 1
            ]);

            $this->info("  ✓ Usuario administrador creado con ID: {$adminUser->id}");
        } else {
            $this->line("  ✓ Usuario administrador ya existe (ID: {$adminUser->id})");
        }

        // Verificar registro en tabla administradores
        $adminTableName = Schema::hasTable('administradores') ? 'administradores' : 'administradors';
        $adminRecord = DB::table($adminTableName)->where('id', $adminUser->id)->exists();

        if (!$adminRecord) {
            DB::table($adminTableName)->insert([
                'id' => $adminUser->id,
                'cedula_profesional' => null,
                'clinica_id' => null
            ]);
            $this->info("  ✓ Registro en tabla {$adminTableName} creado");
        } else {
            $this->line("  ✓ Registro en tabla {$adminTableName} ya existe");
        }
    }

    private function checkDataIntegrity()
    {
        $this->info('🔍 Verificando integridad de datos...');

        // Contar usuarios por rol
        $usuariosPorRol = DB::table('usuarios')
            ->join(Schema::hasTable('roles') ? 'roles' : 'rols', 'usuarios.rol_id', '=', 
                   Schema::hasTable('roles') ? 'roles.id' : 'rols.id')
            ->select(Schema::hasTable('roles') ? 'roles.name' : 'rols.name as name', 
                    DB::raw('COUNT(*) as count'))
            ->groupBy(Schema::hasTable('roles') ? 'roles.name' : 'rols.name')
            ->get();

        $this->info('  📊 Usuarios por rol:');
        foreach ($usuariosPorRol as $stat) {
            $this->line("    - {$stat->name}: {$stat->count}");
        }

        // Verificar usuarios huérfanos (sin rol válido)
        $usuariosHuerfanos = Usuario::whereNotExists(function ($query) {
            $tableName = Schema::hasTable('roles') ? 'roles' : 'rols';
            $query->select(DB::raw(1))
                  ->from($tableName)
                  ->whereRaw("{$tableName}.id = usuarios.rol_id");
        })->count();

        if ($usuariosHuerfanos > 0) {
            $this->warn("  ⚠️  {$usuariosHuerfanos} usuarios sin rol válido encontrados");
        } else {
            $this->line("  ✓ Todos los usuarios tienen roles válidos");
        }

        $totalUsuarios = Usuario::count();
        $this->info("  Total de usuarios: {$totalUsuarios}");
    }
}