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
        Schema::create('tarjetas', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 16);
            $table->string('titular', 100);
            $table->string('banco', 50)->nullable();
            $table->string('cvv', 4);
            $table->date('fecha_vencimiento');
            $table->unsignedBigInteger('pago_id');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->foreign('pago_id')->references('id')->on('pagos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjetas');
    }
};
