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
        Schema::create('atiendes', function (Blueprint $table) {
             $table->unsignedBigInteger('terapeuta_id');
            $table->unsignedBigInteger('cita_id');
            $table->primary(['terapeuta_id', 'cita_id']);
            $table->foreign('terapeuta_id')->references('id')->on('terapeutas');
            $table->foreign('cita_id')->references('id')->on('citas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atiendes');
    }
};
