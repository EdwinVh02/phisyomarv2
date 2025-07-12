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
        Schema::create('respuestas', function (Blueprint $table) {
           $table->id();
            $table->text('texto');
            $table->string('tipo', 30)->nullable();
            $table->unsignedBigInteger('pregunta_id')->nullable();
            $table->unsignedBigInteger('paciente_id')->nullable();
            $table->unsignedBigInteger('cita_id')->nullable();
            $table->unsignedBigInteger('tratamiento_id')->nullable();
            $table->dateTime('fecha_respuesta');
            $table->foreign('pregunta_id')->references('id')->on('preguntas');
            $table->foreign('paciente_id')->references('id')->on('pacientes');
            $table->foreign('cita_id')->references('id')->on('citas');
            $table->foreign('tratamiento_id')->references('id')->on('tratamientos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respuestas');
    }
};
