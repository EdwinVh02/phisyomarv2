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
        Schema::create('experiencia_terapeutas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('terapeuta_id');
            $table->enum('tipo', ['exito','fallo','otro']);
            $table->text('descripcion')->nullable();
            $table->date('fecha')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->foreign('terapeuta_id')->references('id')->on('terapeutas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiencia_terapeutas');
    }
};
