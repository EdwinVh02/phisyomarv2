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
        Schema::create('defines', function (Blueprint $table) {
            $table->unsignedBigInteger('padecimiento_id');
            $table->unsignedBigInteger('tratamiento_id');
            $table->unsignedBigInteger('administrador_id');
            $table->primary(['padecimiento_id', 'tratamiento_id']);
            $table->foreign('padecimiento_id')->references('id')->on('padecimientos');
            $table->foreign('tratamiento_id')->references('id')->on('tratamientos');
            $table->foreign('administrador_id')->references('id')->on('administradors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defines');
    }
};
