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
        Schema::create('paquete_pacientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('paquete_sesion_id');
            $table->date('fecha_compra');
            $table->integer('sesiones_usadas')->default(0);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->foreign('paciente_id')->references('id')->on('pacientes');
            $table->foreign('paquete_sesion_id')->references('id')->on('paquete_sesions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paquete_pacientes');
    }
};
