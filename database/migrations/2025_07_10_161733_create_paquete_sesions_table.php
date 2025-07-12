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
        Schema::create('paquete_sesions', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('numero_sesiones');
            $table->decimal('precio', 8, 2);
            $table->string('tipo_terapia', 100)->nullable();
            $table->string('especifico_enfermedad', 100)->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paquete_sesions');
    }
};
