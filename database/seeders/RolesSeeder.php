<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar la tabla antes de insertar (¡OJO con las foreign keys!)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $roles = [
            ['id' => 1, 'name' => 'Administrador'],
            ['id' => 2, 'name' => 'Terapeuta'],
            ['id' => 3, 'name' => 'Recepcionista'],
            ['id' => 4, 'name' => 'Paciente'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->insert($rol);
        }

        $this->command->info('Roles creados exitosamente:');
        $this->command->info('1. Administrador - Acceso total');
        $this->command->info('2. Terapeuta - Gestión de pacientes asignados');
        $this->command->info('3. Recepcionista - Gestión operativa');
        $this->command->info('4. Paciente - Acceso personal limitado');
    }
}
