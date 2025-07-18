<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename tables to correct Laravel pluralization
        if (Schema::hasTable('especialidads')) {
            Schema::rename('especialidads', 'especialidades');
        }

        if (Schema::hasTable('administradors')) {
            Schema::rename('administradors', 'administradores');
        }

        if (Schema::hasTable('valoracions')) {
            Schema::rename('valoracions', 'valoraciones');
        }

        if (Schema::hasTable('rols')) {
            Schema::rename('rols', 'roles');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert table name changes
        if (Schema::hasTable('especialidades')) {
            Schema::rename('especialidades', 'especialidads');
        }

        if (Schema::hasTable('administradores')) {
            Schema::rename('administradores', 'administradors');
        }

        if (Schema::hasTable('valoraciones')) {
            Schema::rename('valoraciones', 'valoracions');
        }

        if (Schema::hasTable('roles')) {
            Schema::rename('roles', 'rols');
        }
    }
};
