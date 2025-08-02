<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar roles por defecto si no existen
        $roles = [
            ['id' => 1, 'name' => 'Administrador'],
            ['id' => 2, 'name' => 'Terapeuta'],
            ['id' => 3, 'name' => 'Recepcionista'],
            ['id' => 4, 'name' => 'Paciente'],
        ];

        foreach ($roles as $rol) {
            // Solo insertar si no existe ya un rol con ese ID
            $exists = DB::table('roles')->where('id', $rol['id'])->exists();
            if (!$exists) {
                DB::table('roles')->insert($rol);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar solo los roles por defecto
        DB::table('roles')->whereIn('id', [1, 2, 3, 4])->delete();
    }
};