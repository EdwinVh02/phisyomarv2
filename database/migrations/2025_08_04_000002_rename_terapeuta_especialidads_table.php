<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('terapeuta_especialidads')) {
            Schema::rename('terapeuta_especialidads', 'terapeuta_especialidad');
        }

        if (Schema::hasTable('terapeuta_especialidad')) {
            if (Schema::hasColumn('terapeuta_especialidad', 'TerapeutaId')) {
                Schema::table('terapeuta_especialidad', function (Blueprint $table) {
                    $table->renameColumn('TerapeutaId', 'terapeuta_id');
                });
            }

            if (Schema::hasColumn('terapeuta_especialidad', 'EspecialidadId')) {
                Schema::table('terapeuta_especialidad', function (Blueprint $table) {
                    $table->renameColumn('EspecialidadId', 'especialidad_id');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('terapeuta_especialidad')) {
            if (Schema::hasColumn('terapeuta_especialidad', 'especialidad_id')) {
                Schema::table('terapeuta_especialidad', function (Blueprint $table) {
                    $table->renameColumn('especialidad_id', 'EspecialidadId');
                });
            }

            if (Schema::hasColumn('terapeuta_especialidad', 'terapeuta_id')) {
                Schema::table('terapeuta_especialidad', function (Blueprint $table) {
                    $table->renameColumn('terapeuta_id', 'TerapeutaId');
                });
            }

            Schema::rename('terapeuta_especialidad', 'terapeuta_especialidads');
        }
    }
};
