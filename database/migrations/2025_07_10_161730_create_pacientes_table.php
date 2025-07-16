<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Asegúrate que la migración de usuarios corre antes que esta migración
        Schema::create('pacientes', function (Blueprint $table) {
            // ID del paciente es FK y PK, debe coincidir con usuarios.id
            $table->unsignedBigInteger('id')->primary();
            $table->string('contacto_emergencia_nombre', 100)->nullable();
            $table->string('contacto_emergencia_telefono', 20)->nullable();
            $table->string('contacto_emergencia_parentesco', 50)->nullable();
            $table->string('tutor_nombre', 100)->nullable();
            $table->string('tutor_telefono', 20)->nullable();
            $table->string('tutor_parentesco', 50)->nullable();
            $table->string('tutor_direccion', 150)->nullable();
            $table->unsignedBigInteger('historial_medico_id')->nullable();
            $table->timestamps();

            // Índice para FK
            $table->index('historial_medico_id');

            // Foreign Key: usuarios.id debe ser unsignedBigInteger también
            $table->foreign('id')->references('id')->on('usuarios')->onDelete('cascade');

            // Foreign Key opcional para historial_medico si existe esa tabla
            // $table->foreign('historial_medico_id')->references('id')->on('historial_medicos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
