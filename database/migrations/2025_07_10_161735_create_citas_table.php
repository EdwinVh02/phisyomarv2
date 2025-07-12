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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora');
            $table->string('tipo', 50)->nullable();
            $table->integer('duracion')->nullable();
            $table->string('ubicacion', 100)->nullable();
            $table->string('equipo_asignado', 100)->nullable();
            $table->text('motivo')->nullable();
            $table->enum('estado', ['agendada', 'atendida', 'cancelada', 'no_asistio', 'reprogramada']);
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('terapeuta_id')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->unsignedBigInteger('paquete_paciente_id')->nullable();
            $table->text('observaciones')->nullable();
            $table->integer('escala_dolor_eva_inicio')->nullable();
            $table->integer('escala_dolor_eva_fin')->nullable();
            $table->text('como_fue_lesion')->nullable();
            $table->text('antecedentes_patologicos')->nullable();
            $table->text('antecedentes_no_patologicos')->nullable();
            $table->timestamps();
            $table->foreign('paciente_id')->references('id')->on('pacientes');
            $table->foreign('terapeuta_id')->references('id')->on('terapeutas');
            $table->foreign('registro_id')->references('id')->on('registros');
            $table->foreign('paquete_paciente_id')->references('id')->on('paquete_pacientes');
            $table->index('fecha_hora');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
