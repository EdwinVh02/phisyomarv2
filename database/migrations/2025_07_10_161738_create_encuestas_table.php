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
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 100);
            $table->unsignedBigInteger('recepcionista_id')->nullable();
            $table->enum('tipo', ['satisfaccion', 'dolor', 'otro']);
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->foreign('recepcionista_id')->references('id')->on('recepcionistas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encuestas');
    }
};
