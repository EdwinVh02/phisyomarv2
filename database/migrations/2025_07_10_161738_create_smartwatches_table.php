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
        Schema::create('smartwatches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('valoracion_id')->nullable();
            $table->unsignedBigInteger('paciente_id')->nullable();
            $table->text('datos')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->foreign('valoracion_id')->references('id')->on('valoracions');
            $table->foreign('paciente_id')->references('id')->on('pacientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smartwatches');
    }
};
