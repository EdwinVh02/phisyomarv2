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
        Schema::create('terapeutas', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('cedula_profesional', 30)->nullable();
            $table->string('especialidad_principal', 100)->nullable();
            $table->integer('experiencia_anios')->nullable();
            $table->enum('estatus', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->foreign('id')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terapeutas');
    }
};
