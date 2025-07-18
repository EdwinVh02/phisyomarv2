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
        Schema::create('valoracions', function (Blueprint $table) {
            $table->id();
            $table->integer('puntuacion');
            $table->dateTime('fecha_hora');
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('terapeuta_id')->nullable();
            $table->foreign('paciente_id')->references('id')->on('pacientes');
            $table->foreign('terapeuta_id')->references('id')->on('terapeutas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valoracions');
    }
};
