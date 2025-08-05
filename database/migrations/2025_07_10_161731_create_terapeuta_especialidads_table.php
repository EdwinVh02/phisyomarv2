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
        Schema::create('terapeuta_especialidad', function (Blueprint $table) {
            $table->unsignedBigInteger('terapeuta_id');
            $table->unsignedBigInteger('especialidad_id');
            $table->primary(['terapeuta_id', 'especialidad_id']);
            $table->foreign('terapeuta_id')->references('id')->on('terapeutas');
            $table->foreign('especialidad_id')->references('id')->on('especialidades');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terapeuta_especialidad');
    }
};
