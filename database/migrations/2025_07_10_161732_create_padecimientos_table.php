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
        Schema::create('padecimientos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('sintomas')->nullable();
            $table->string('clasificacion', 100)->nullable();
            $table->string('nivel_gravedad', 30)->nullable();
            $table->string('codigo_cie10', 20)->nullable();
            $table->string('origen', 100)->nullable();
            $table->text('estudios_imagen')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('padecimientos');
    }
};
