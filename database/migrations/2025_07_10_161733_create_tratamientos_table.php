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
        Schema::create('tratamientos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 100);
            $table->text('descripcion')->nullable();
            $table->integer('duracion')->nullable();
            $table->string('frecuencia', 50)->nullable();
            $table->text('requisitos')->nullable();
            $table->unsignedBigInteger('padecimiento_id')->nullable();
            $table->unsignedBigInteger('tarifa_id')->nullable();
            $table->timestamps();
            $table->foreign('padecimiento_id')->references('id')->on('padecimientos');
            $table->foreign('tarifa_id')->references('id')->on('tarifas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
};
