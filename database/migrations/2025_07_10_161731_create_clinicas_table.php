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
        Schema::create('clinicas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('direccion', 150);
            $table->string('razon_social', 150);
            $table->string('rfc', 20);
            $table->string('no_licencia_sanitaria', 50)->nullable();
            $table->string('no_registro_patronal', 50)->nullable();
            $table->string('no_aviso_de_funcionamiento', 50)->nullable();
            $table->string('colores_corporativos', 50)->nullable();
            $table->string('logo_url', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinicas');
    }
};
