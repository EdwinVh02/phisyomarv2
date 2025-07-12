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
        Schema::create('consentimiento_informados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paciente_id');
            $table->date('fecha_firma');
            $table->string('tipo', 100)->nullable();
            $table->string('documento_url', 250)->nullable();
            $table->string('firmado_por', 100)->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->foreign('paciente_id')->references('id')->on('pacientes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consentimiento_informados');
    }
};
