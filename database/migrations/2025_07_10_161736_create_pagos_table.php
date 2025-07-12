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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora');
            $table->decimal('monto', 8, 2);
            $table->enum('forma_pago', ['efectivo', 'transferencia', 'terminal']);
            $table->string('recibo', 100)->nullable();
            $table->unsignedBigInteger('cita_id')->nullable();
            $table->unsignedBigInteger('paquete_paciente_id')->nullable();
            $table->string('autorizacion', 100)->nullable();
            $table->boolean('factura_emitida')->default(false);
            $table->timestamps();
            $table->foreign('cita_id')->references('id')->on('citas');
            $table->foreign('paquete_paciente_id')->references('id')->on('paquete_pacientes');
            $table->index('fecha_hora');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
