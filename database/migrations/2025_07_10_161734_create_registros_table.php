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
        Schema::create('registros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('historial_medico_id');
            $table->dateTime('fecha_hora')->nullable();
            $table->text('antecedentes')->nullable();
            $table->text('medicacion_actual')->nullable();
            $table->text('postura')->nullable();
            $table->text('marcha')->nullable();
            $table->text('fuerza_muscular')->nullable();
            $table->text('rango_movimiento_muscular_rom')->nullable();
            $table->text('tono_muscular')->nullable();
            $table->text('localizacion_dolor')->nullable();
            $table->integer('intensidad_dolor')->nullable();
            $table->string('tipo_dolor', 100)->nullable();
            $table->text('movilidad_articular')->nullable();
            $table->text('balance_y_coordinacion')->nullable();
            $table->text('sensibilidad')->nullable();
            $table->text('reflejos_osteotendinosos')->nullable();
            $table->text('motivo_visita')->nullable();
            $table->integer('numero_sesion')->nullable();
            $table->timestamps();
            $table->foreign('historial_medico_id')->references('id')->on('historial_medicos');
            $table->index('historial_medico_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};
