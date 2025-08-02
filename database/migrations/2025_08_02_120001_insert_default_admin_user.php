<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar que existe el rol de administrador
        $adminRoleExists = DB::table('roles')->where('id', 1)->where('name', 'Administrador')->exists();
        
        if (!$adminRoleExists) {
            // Si no existe el rol, crearlo primero
            DB::table('roles')->insert([
                'id' => 1,
                'name' => 'Administrador'
            ]);
        }

        // Verificar si ya existe un usuario administrador
        $adminExists = DB::table('usuarios')
            ->where('correo_electronico', 'admin@phisyomar.com')
            ->orWhere('rol_id', 1)
            ->exists();

        if (!$adminExists) {
            // Crear usuario administrador
            $adminUserId = DB::table('usuarios')->insertGetId([
                'nombre' => 'Admin',
                'apellido_paterno' => 'Sistema',
                'apellido_materno' => 'PhisyoMar',
                'correo_electronico' => 'admin@phisyomar.com',
                'contraseña' => Hash::make('admin123456'), // Contraseña hasheada
                'telefono' => '0000000000',
                'direccion' => 'Sistema',
                'fecha_nacimiento' => '1990-01-01',
                'sexo' => 'Otro',
                'curp' => 'ADMI900101HDFXXX01', // CURP temporal único
                'ocupacion' => 'Administrador del Sistema',
                'estatus' => 'activo',
                'rol_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Crear registro en tabla administradores
            DB::table('administradores')->insert([
                'id' => $adminUserId,
                'cedula_profesional' => null,
                'clinica_id' => null
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el usuario administrador por defecto
        $adminUser = DB::table('usuarios')->where('correo_electronico', 'admin@phisyomar.com')->first();
        
        if ($adminUser) {
            // Eliminar de tabla administradores primero (foreign key)
            DB::table('administradores')->where('id', $adminUser->id)->delete();
            
            // Eliminar usuario
            DB::table('usuarios')->where('id', $adminUser->id)->delete();
        }
    }
};