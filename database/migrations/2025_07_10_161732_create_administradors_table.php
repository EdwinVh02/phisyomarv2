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
        Schema::create('administradors', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('cedula_profesional', 30)->nullable();
            $table->unsignedBigInteger('clinica_id')->nullable();
            $table->foreign('id')->references('id')->on('usuarios');
            $table->foreign('clinica_id')->references('id')->on('clinicas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administradors');
    }
};
